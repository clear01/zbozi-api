<?php
declare(strict_types = 1);

namespace Clear01\ZboziApi\Integration;

use Clear01\ZboziApi\Model\ZboziApiException;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ApiClient
{
	/** Minimal delay between two requests [s] – review endpoints allow 1 request per second. */
	const REQUEST_INTERVAL = 1.0;

	/** How many times a request is repeated after HTTP 429. */
	const MAX_RETRIES = 3;

	/** Maximal accepted Retry-After [s]. */
	const MAX_RETRY_AFTER = 60;

	/** Access token is renewed this many seconds before its expiration. */
	const TOKEN_EXPIRATION_RESERVE = 60;

	/** @var ApiConfig */
	protected $config;

	/** @var Client|null */
	protected $guzzleClient;

	/** @var array */
	private $guzzleConfig = [
		"http_errors" => false
	];

	/** @var string|null */
	private $accessToken;

	/** @var int|null */
	private $accessTokenExpiresAt;

	/** @var float|null */
	private $lastRequestTime;

	public function __construct(ApiConfig $config)
	{
		$this->config = $config;
	}

	/**
	 * @param array $query query parameters, `premiseId` is added automatically
	 * @throws ZboziApiException
	 */
	public function sendRequest(string $method, string $path, ?string $body = null, array $query = []): ResponseInterface {
		$query['premiseId'] = $this->config->getPremiseId();
		$uri = (new Uri($this->buildUrl($path)))->withQuery(http_build_query($query, '', '&', PHP_QUERY_RFC3986));

		$headers = ['Accept' => 'application/json'];
		if($body !== null) {
			$headers['Content-Type'] = 'application/json';
		}
		$request = new Request($method, $uri, $headers, $body);

		$tokenRenewed = false;
		for($attempt = 0; ; $attempt++) {
			$response = $this->send($this->setupRequest($request));

			if($response->getStatusCode() === 401 && !$tokenRenewed) {
				// access token may have been revoked or expired earlier than announced
				$this->accessToken = null;
				$tokenRenewed = true;
				continue;
			}
			if($response->getStatusCode() === 429 && $attempt < self::MAX_RETRIES) {
				$this->sleep($this->getRetryAfter($response));
				continue;
			}
			return $response;
		}
	}

	public function setGuzzleConfig(array $config) {
		if($this->guzzleClient) {
			throw new \RuntimeException('Guzzle client already initialized. Config can be set before the first request is called.');
		}
		$this->guzzleConfig = array_merge($this->guzzleConfig, $config);
	}

	protected final function initClient() {
		if(!$this->guzzleClient) {
			$this->guzzleClient = new Client($this->guzzleConfig);
		}
	}

	/**
	 * @throws ZboziApiException
	 */
	protected function setupRequest(RequestInterface $request): RequestInterface
	{
		return $request->withHeader('Authorization', 'Bearer ' . $this->getAccessToken());
	}

	/**
	 * Exchanges the API key (refresh token) for a short-lived access token.
	 * @throws ZboziApiException
	 */
	protected function getAccessToken(): string
	{
		if($this->accessToken !== null && $this->accessTokenExpiresAt > time() + self::TOKEN_EXPIRATION_RESERVE) {
			return $this->accessToken;
		}

		$request = new Request(
			'POST',
			$this->buildUrl('/user/token'),
			[
				'Accept' => 'application/json',
				'Content-Type' => 'application/x-www-form-urlencoded',
				'Authorization' => 'Bearer ' . $this->config->getApiKey(),
			],
			http_build_query(['grant_type' => 'client_credentials'])
		);
		$response = $this->send($request);
		if($response->getStatusCode() !== 200) {
			CommonErrorsHandler::handleResponse($response);
		}

		$data = ContentParser::parseBody((string) $response->getBody());
		if(empty($data['access_token'])) {
			throw new ZboziApiException('Access token missing in token response.');
		}
		$this->accessToken = (string) $data['access_token'];
		$this->accessTokenExpiresAt = time() + (int) ($data['expires_in'] ?? 0);
		return $this->accessToken;
	}

	private function send(RequestInterface $request): ResponseInterface
	{
		if($this->lastRequestTime !== null) {
			$elapsed = microtime(true) - $this->lastRequestTime;
			if($elapsed < self::REQUEST_INTERVAL) {
				$this->sleep(self::REQUEST_INTERVAL - $elapsed);
			}
		}

		$this->initClient();
		try {
			return $this->guzzleClient->send($request);
		} finally {
			$this->lastRequestTime = microtime(true);
		}
	}

	private function buildUrl(string $path): string
	{
		return rtrim($this->config->getEndpointUrl(), '/') . '/' . ltrim($path, '/');
	}

	private function getRetryAfter(ResponseInterface $response): float
	{
		$retryAfter = $response->getHeaderLine('Retry-After');
		$seconds = is_numeric($retryAfter) ? (float) $retryAfter : self::REQUEST_INTERVAL;
		return max(self::REQUEST_INTERVAL, min($seconds, self::MAX_RETRY_AFTER));
	}

	protected function sleep(float $seconds): void
	{
		usleep((int) ($seconds * 1000000));
	}

}
