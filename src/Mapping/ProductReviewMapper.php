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
		$dataAccessor = new DataAccessor($data, ['createDatetime', 'productData', 'productReviewId', 'state']);
		$dataAccessor->setConsiderEmptyStringAsNull(true);
		return new ProductReview(
			$dataAccessor->getDateTime('createDatetime'),
			$dataAccessor->getDateTime('editDatetime'),
			$dataAccessor->get('negativeComment') ?? '',
			$dataAccessor->get('positiveComment') ?? '',
			ProductDataMapper::buildFromFlatData($dataAccessor->get('productData')),
			(string) $dataAccessor->get('productReviewId'),
			$dataAccessor->get('ratingStars'),
			ReviewStateMapper::buildFromString($dataAccessor->get('state')),
			$dataAccessor->get('text'),
			$dataAccessor->get('userName')
		);
	}
}
