<?php
declare(strict_types = 1);

namespace Clear01\ZboziApi\Model;

class ShopReactionState
{
	const TYPE_APPROVED = 'approved';
	const TYPE_NEW = 'new';
	const TYPE_DENIED = 'denied';

	/** @var string */
	protected $state;

	private function __construct(string $state) {
		// disable public constructor
		$this->state = $state;
	}

	public static function createApproved() {
		return new self(self::TYPE_APPROVED);
	}

	public static function createNew() {
		return new self(self::TYPE_NEW);
	}

	public static function createDenied() {
		return new self(self::TYPE_DENIED);
	}

	public function isApproved(): bool {
		return $this->state === self::TYPE_APPROVED;
	}

	public function isNew(): bool {
		return $this->state === self::TYPE_NEW;
	}

	public function isDenied() {
		return $this->state === self::TYPE_DENIED;
	}

	public function __toString()
	{
		return $this->state;
	}
}