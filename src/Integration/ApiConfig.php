<?php
declare(strict_types = 1);

namespace Clear01\ZboziApi\Integration;

/**
 * Configuration of Sklik API Fénix (https://api.sklik.cz/v1/openapi.json).
 *
 * $shopId is the Zboží.cz shop ID, sent as `premiseId` with every request.
 * $apiKey is the Fénix API key (refresh token) generated in Sklik → Settings → API.
 */
class ApiConfig
{
	/** @var int */
	protected $shopId;

	/** @var string */
	protected $apiKey;

	public function __construct(int $shopId, string $apiKey)
	{
		$this->shopId = $shopId;
		$this->apiKey = $apiKey;
	}

	public function getShopId(): int
	{
		return $this->shopId;
	}

	public function getPremiseId(): int
	{
		return $this->shopId;
	}

	public function getApiKey(): string
	{
		return $this->apiKey;
	}

	public function getEndpointUrl(): string {
		return 'https://api.sklik.cz/v1';
	}

}
