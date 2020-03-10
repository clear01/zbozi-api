<?php
declare(strict_types = 1);

namespace Clear01\ZboziApi\Mapping;

use Clear01\ZboziApi\Model\ProductData;

class ProductDataMapper
{
	/**
	 * @throws \Clear01\ZboziApi\Model\ZboziApiException
	 */
	public static function buildFromFlatData(array $data): ProductData
	{
		$dataAccessor = new DataAccessor($data, ['cartProductName', 'itemId']);
		$dataAccessor->setConsiderEmptyStringAsNull(true);
		return new ProductData(
			$dataAccessor->get('cartProductName'),
			$dataAccessor->get('itemId'),
			$dataAccessor->get('productId'),
			$dataAccessor->get('productName')
		);
	}
}