<?php
declare(strict_types = 1);

namespace Clear01\ZboziApi\Model;

class ReviewState
{
	const TYPE_APPROVED = 'approved';
	const TYPE_NEW = 'new';

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

	public function isApproved(): bool {
		return $this->state === self::TYPE_APPROVED;
	}

	public function isNew(): bool {
		return $this->state === self::TYPE_NEW;
	}

	public function __toString()
	{
		return $this->state;
	}

}