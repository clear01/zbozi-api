<?php
declare(strict_types = 1);

namespace Clear01\ZboziApi\Mapping;

use Clear01\ZboziApi\Model\ShopReactionState;
use Clear01\ZboziApi\Model\ZboziApiException;

class ShopReactionStateMapper
{
	/**
	 * @throws ZboziApiException
	 */
	public static function buildFromString(string $data): ShopReactionState {
		switch($data) {
			case 'approved':
				return ShopReactionState::createApproved();
			case 'new':
				return ShopReactionState::createNew();
			case 'denied':
				return ShopReactionState::createDenied();
			default:
				throw new ZboziApiException('Unknown state ' . $data);
		}
	}
}