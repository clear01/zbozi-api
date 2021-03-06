<?php
declare(strict_types = 1);

namespace Clear01\ZboziApi\Integration;

use Clear01\ZboziApi\Model\ZboziApiException;
use Psr\Http\Message\ResponseInterface;

class CommonErrorsHandler
{
	public static function handleResponse(ResponseInterface $response) {
		$errorMessages = [];
		$body = $response->getBody()->getContents();
		if($body && ($responseData = json_decode($body, true)) !== false) {
			if(isset($responseData['errors'])) {
				foreach($responseData['errors'] as $errorData) {
					if(isset($errorData['messages'])) {
						$errorMessages = array_unique(array_merge($errorMessages, $errorData['messages']));
					}
				}
			}
		}
		$messageSuffix = count($errorMessages) ? (' ' . implode(' ', $errorMessages)) : '';

		switch($response->getStatusCode()) {
			case 400:
				throw new ZboziApiException('Error in input argument.' . $messageSuffix);
			case 401:
				throw new ZboziApiException('Unauthorized.' . $messageSuffix);
			case 403:
				throw new ZboziApiException('Access forbidden. User does not have access to given resource.' . $messageSuffix);
			case 404:
				throw new ZboziApiException('Not found.' . $messageSuffix);
			case 429:
				throw new ZboziApiException('Too many requests. Your shop reached this method\'s limit.' . $messageSuffix);
			case 500:
				throw new ZboziApiException('Internal server error.' . $messageSuffix);
			case 504:
				throw new ZboziApiException('Gateway Timeout.' . $messageSuffix);
			case 200:
				return;
		}

		throw new ZboziApiException('Unknown error - HTTP response code ' . $response->getStatusCode());
	}
}