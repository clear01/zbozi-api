<?php
declare(strict_types = 1);

namespace Clear01\ZboziApi\Mapping;

use Clear01\ZboziApi\Model\ProductReview;

class ProductReviewMapper
{
	/**
	 * @throws \Clear01\ZboziApi\Model\ZboziApiException
	 */
	public static function buildFromFlatData(array $data): ProductReview
	{
		$dataAccessor = new DataAccessor($data, ['createTimestamp', 'negativeComments', 'positiveComments', 'productData', 'productReviewId', 'state']);
		$dataAccessor->setConsiderEmptyStringAsNull(true);
		return new ProductReview(
			\DateTimeImmutable::createFromFormat('U', (string) $dataAccessor->get('createTimestamp')),
			!$dataAccessor->get('editTimestamp') ? null : \DateTimeImmutable::createFromFormat('U', (string) $dataAccessor->get('editTimestamp')),
			$dataAccessor->get('negativeComments') ?? '',
			$dataAccessor->get('positiveComments') ?? '',
			ProductDataMapper::buildFromFlatData($dataAccessor->get('productData')),
			$dataAccessor->get('productReviewId'),
			$dataAccessor->get('ratingStars'),
			ReviewStateMapper::buildFromString($dataAccessor->get('state')),
			$dataAccessor->get('text'),
			$dataAccessor->get('userName')
		);
	}
}