<?php
declare(strict_types = 1);

namespace Clear01\ZboziApi\Model;

class ProductData
{
	/** @var string */
	protected $cartProductName;

	/** @var string */
	protected $itemId;

	/** @var int|null */
	protected $productId;

	/** @var string|null */
	protected $productName;

	public function __construct(string $cartProductName, string $itemId, int $productId, string $productName)
	{
		$this->cartProductName = $cartProductName;
		$this->itemId = $itemId;
		$this->productId = $productId;
		$this->productName = $productName;
	}

	public function getCartProductName(): string
	{
		return $this->cartProductName;
	}

	public function getItemId(): string
	{
		return $this->itemId;
	}

	public function getProductId(): int
	{
		return $this->productId;
	}

	public function getProductName(): string
	{
		return $this->productName;
	}

}