<?php
declare(strict_types = 1);

namespace Clear01\ZboziApi\Mapping;

use Clear01\ZboziApi\Model\Satisfaction;

class SatisfactionMapper
{
	/**
	 * @throws \Clear01\ZboziApi\Model\ZboziApiException
	 */
	public static function buildFromFlatData($data)
	{
		$dataAccessor = new DataAccessor($data, ['overall']);
		$dataAccessor->setConsiderEmptyStringAsNull(true);
		return new Satisfaction(
			($answer = $dataAccessor->get('communication')) ? ($answer === 'yes' ? 1 : 0) : null,
			($answer = $dataAccessor->get('deliveryDate')) ? ($answer === 'yes' ? 1 : 0) : null,
			($answer = $dataAccessor->get('deliveryQuality')) ? ($answer === 'yes' ? 1 : 0) : null,
			($answer = $dataAccessor->get('deliveryQuality')) === 'yes' ? 2 : ($answer === 'yes_but' ? 1 : 0)
		);
	}
}