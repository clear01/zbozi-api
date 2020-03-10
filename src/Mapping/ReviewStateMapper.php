<?php
declare(strict_types = 1);

namespace Clear01\ZboziApi\Mapping;

use Clear01\ZboziApi\Model\ReviewState;
use Clear01\ZboziApi\Model\ZboziApiException;

class ReviewStateMapper
{
	/**
	 * @throws ZboziApiException
	 */
	public static function buildFromString(string $data): ReviewState {
		switch($data) {
			case 'approved':
				return ReviewState::createApproved();
			case 'new':
				return ReviewState::createNew();
			default:
				throw new ZboziApiException('Unknown state ' . $data);
		}
	}
}