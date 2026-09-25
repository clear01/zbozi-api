<?php
declare(strict_types = 1);

namespace Clear01\ZboziApi\Integration;

use Clear01\ZboziApi\Model\ZboziApiException;
use Psr\Http\Message\ResponseInterface;

class CommonErrorsHandler
{
	/**
	 * @throws ZboziApiException
	 */
	public static function handleResponse(ResponseInterface $response) {
		$errorMessages = self::getErrorMessages((string) $response->getBody());
		$messageSuffix = count($errorMessages) ? (' ' . implode(' ', $errorMessages)) : '';

		switch($response->getStatusCode()) {
			case 200:
			case 201:
				return;
			case 400:
				throw new ZboziApiException('Error in input argument.' . $messageSuffix);
			case 401:
				throw new ZboziApiException('Unauthorized.' . $messageSuffix);
			case 403:
				throw new ZboziApiException('Access forbidden. User does not have access to given resource.' . $messageSuffix);
			case 404:
				throw new ZboziApiException('Not found.' . $messageSuffix);
			case 410:
				throw new ZboziApiException('Gone. The API method is no longer available.' . $messageSuffix);
			case 422:
				throw new ZboziApiException('Validation error.' . $messageSuffix);
			case 429:
				throw new ZboziApiException('Too many requests. Your shop reached this method\'s limit.' . $messageSuffix);
			case 500:
				throw new ZboziApiException('Internal server error.' . $messageSuffix);
			case 504:
				throw new ZboziApiException('Gateway Timeout.' . $messageSuffix);
		}

		throw new ZboziApiException('Unknown error - HTTP response code ' . $response->getStatusCode() . '.' . $messageSuffix);
	}

	/**
	 * Supports Fénix format `{"detail": [{"loc": [...], "msg": "..."}]}` or `{"detail": "..."}`
	 * and legacy Zboží format `{"errors": [{"messages": [...]}]}`.
	 * @return string[]
	 */
	private static function getErrorMessages(string $body): array {
		$responseData = $body ? json_decode($body, true) : null;
		if(!is_array($responseData)) {
			return [];
		}

		$errorMessages = [];
		if(isset($responseData['detail'])) {
			$details = is_array($responseData['detail']) ? $responseData['detail'] : [$responseData['detail']];
			foreach($details as $detail) {
				if(is_string($detail)) {
					$errorMessages[] = $detail;
				} elseif(isset($detail['msg'])) {
					$location = isset($detail['loc']) && is_array($detail['loc']) ? implode('.', $detail['loc']) . ': ' : '';
					$errorMessages[] = $location . $detail['msg'];
				}
			}
		}
		if(isset($responseData['errors']) && is_array($responseData['errors'])) {
			foreach($responseData['errors'] as $errorData) {
				if(isset($errorData['messages'])) {
					$errorMessages = array_merge($errorMessages, (array) $errorData['messages']);
				}
			}
		}
		return array_values(array_unique($errorMessages));
	}
}
