<?php
declare(strict_types = 1);

namespace Clear01\ZboziApi\Integration;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ApiClient
{
	/** @var ApiConfig */
	protected $config;

	/** @var Client|null */
	protected $guzzleClient;

	/** @var array */
	private $guzzleConfig = [];

	public function __construct(ApiConfig $config)
	{
		$this->config = $config;
	}

	public function sendRequest(string $method, string $path, ?string $body = null): ResponseInterface {
		$request = new Request(
			$method,
			rtrim($this->config->getEndpointUrl(), '/') . '/' . ltrim($path, '/'),
			[],
			$body
		);

		$this->initClient();
		return $this->guzzleClient->send(
			$this->setupRequest($request)
		);
	}

	public function setGuzzleConfig(array $config) {
		if($this->guzzleClient) {
			throw new \RuntimeException('Guzzle client already initialized. Config can be set before the first request is called.');
		}
		$this->guzzleConfig = $config;
	}

	protected final function initClient() {
		if(!$this->guzzleClient) {
			$this->guzzleClient = new Client($this->guzzleConfig);
		}
	}

	protected function setupRequest(Request $request): RequestInterface
	{
		return $request->withAddedHeader(
				'Authorization',
				'Basic ' . base64_encode($this->config->getShopId() . ':' . $this->config->getApiKey())
			);
	}

}