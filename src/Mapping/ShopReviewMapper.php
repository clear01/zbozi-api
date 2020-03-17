<?php
declare(strict_types = 1);

namespace Clear01\ZboziApi\Mapping;

use Clear01\ZboziApi\Model\ShopReview;

class ShopReviewMapper
{
	/**
	 * @throws \Clear01\ZboziApi\Model\ZboziApiException
	 */
	public static function buildFromFlatData(array $data): ShopReview
	{
		$dataAccessor = new DataAccessor($data, ['createTimestamp', 'negativeComment', 'positiveComment', 'productData', 'productReviewId', 'state']);
		$dataAccessor->setConsiderEmptyStringAsNull(true);
		return new ShopReview(
			\DateTimeImmutable::createFromFormat('U', (string) $dataAccessor->get('createTimestamp')),
			$dataAccessor->get('negativeComment') ?: '',
			$dataAccessor->get('positiveComment') ?: '',
			$dataAccessor->get('orderId'),
			SatisfactionMapper::buildFromFlatData($dataAccessor->get('satisfaction')),
			$dataAccessor->get('shopReaction') ?: null,
			($reactionState = $dataAccessor->get('shopReactionState')) ? ShopReactionStateMapper::buildFromString($reactionState) : null,
			$dataAccessor->get('shopReviewId'),
			ReviewStateMapper::buildFromString($dataAccessor->get('state')),
			$dataAccessor->get('userName')
		);
	}
}