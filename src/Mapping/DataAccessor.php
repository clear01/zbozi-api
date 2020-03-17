<?php
declare(strict_types = 1);

namespace Clear01\ZboziApi\Mapping;

use Clear01\ZboziApi\Model\ZboziApiException;

class DataAccessor
{
	/** @var array */
	protected $data;

	/** @var ?array */
	protected $mandatoryFields;

	/** @var bool  */
	protected $considerEmptyStringAsNull = false;

	public function __construct(array $data, ?array $mandatoryFields = null)
	{
		$this->data = $data;
		$this->mandatoryFields = $mandatoryFields;
	}

	/**
	 * @param string $attribute
	 * @return mixed
	 * @throws ZboziApiException
	 */
	public function get(string $attribute) {
		if(!isset($this->data[$attribute])) {
			if ($this->mandatoryFields && in_array($attribute, $this->mandatoryFields)) {
				throw new ZboziApiException('Attribute ' . $attribute . ' is mandatory, but was not included in API response.');
			}
			return null;
		}

		return $this->normalizeData($this->data[$attribute]);
	}

	public function setConsiderEmptyStringAsNull(bool $considerEmptyStringAsNull): void
	{
		$this->considerEmptyStringAsNull = $considerEmptyStringAsNull;
	}

	protected function normalizeData($value)
	{
		if($this->considerEmptyStringAsNull && is_string($value) && !strlen($value)) {
			return null;
		}
		return $value;
	}

}