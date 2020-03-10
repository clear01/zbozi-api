<?php
declare(strict_types = 1);

namespace Clear01\ZboziApi\Integration;

use Clear01\ZboziApi\Model\ZboziApiException;

class ContentParser
{
	public static function parseBody(string $body): array {
		if(!$body || ($bodyData = json_decode($body, true)) === false) {
			throw new ZboziApiException('Empty body or JSON deserialization failure.');
		}
		return $bodyData;
	}
}