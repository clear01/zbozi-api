<?php
declare(strict_types = 1);

namespace Clear01\ZboziApi\Integration;

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

	public function getApiKey(): string
	{
		return $this->apiKey;
	}

	public function getEndpointUrl(): string {
		return 'https://api.zbozi.cz';
	}

}