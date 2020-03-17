<?php
declare(strict_types = 1);

namespace Clear01\ZboziApi\Facade;

use Clear01\ZboziApi\Integration\ApiClient;
use Clear01\ZboziApi\Integration\CommonErrorsHandler;
use Clear01\ZboziApi\Integration\ContentParser;
use Clear01\ZboziApi\Mapping\ProductReviewMapper;
use Clear01\ZboziApi\Mapping\ShopReviewMapper;
use Clear01\ZboziApi\Model\ProductReview;
use Clear01\ZboziApi\Model\ShopReview;
use Clear01\ZboziApi\Model\ZboziApiException;
use Psr\Http\Message\ResponseInterface;

class ReviewsFacade
{
	const MAX_REACTION_LENGTH = 10000;

	/** @var ApiClient */
	protected $apiClient;

	public function __construct(ApiClient $apiClient)
	{
		$this->apiClient = $apiClient;
	}

	/**
	 * @return ProductReview[]
	 * @throws ZboziApiException
	 * @throws \Throwable
	 */
	public function getProductReviews(\DateTimeInterface $fromDate, ?int $limit): array {
		$query = '?timestampFrom=' . $fromDate->format('U');
		if($limit) {
			$query .= '&limit=' . $limit;
		}
		$response = $this->apiClient->sendRequest('GET', '/v1/shop/product-reviews' . $query);

		$productReviews = [];
		if($response->getStatusCode() === 200) {
			$data = ContentParser::parseBody($response->getBody()
													  ->getContents());
			if (!isset($data['data'])) {
				throw new ZboziApiException('Invalid response');
			}
			foreach ($data['data'] as $record) {
				$productReviews[] = ProductReviewMapper::buildFromFlatData($record);
			}
		} elseif($response->getStatusCode() === 404) {
			return [];
		} else {
			CommonErrorsHandler::handleResponse($response);
		}
		return $productReviews;
	}

	/**
	 * @return ShopReview[]
	 * @throws ZboziApiException
	 * @throws \Throwable
	 */
	public function getShopReviews(\DateTimeInterface $fromDate, ?\DateTimeInterface $toDate, ?int $limit, ?int $offset): array {
		// todo implement automatic limit and offset configuration to get all available results? (initial import)
		$query = '?timestampFrom=' . $fromDate->format('U');
		if($toDate) {
			$query .= '&timestampTo=' . $toDate->format('U');
		}
		if($limit) {
			$query .= '&limit=' . $limit;
		}
		if($offset) {
			$query .= '&offset=' . $offset;
		}
		$response = $this->apiClient->sendRequest('GET', '/v1/shop/reviews' . $query);
		return $this->getShopReviewResults($response);
	}

	/**
	 * @return ShopReview[]
	 * @throws ZboziApiException
	 * @throws \Throwable
	 */
	public function getShopReviewById(int $reviewId): ?ShopReview {
		$response = $this->apiClient->sendRequest('GET', '/v1/shop/reviews/' . $reviewId);
		$result = end($this->getShopReviewResults($response));
		return $result ? $result : null;
	}

	/**
	 * @return ShopReview[]
	 * @throws ZboziApiException
	 */
	protected function getShopReviewResults(ResponseInterface $response) {
		$shopReviews = [];
		if($response->getStatusCode() === 200 || $response->getStatusCode() === 206) {
			$data = ContentParser::parseBody($response->getBody()->getContents());
			if(!isset($data['data'])) {
				throw new ZboziApiException('Invalid response');
			}
			foreach($data['data'] as $record) {
				$shopReviews[] = ShopReviewMapper::buildFromFlatData($record);
			}
		} else {
			CommonErrorsHandler::handleResponse($response);
		}
		return $shopReviews;
	}

	/**
	 * @param int $reviewId
	 * @param string $reaction UTF-8 encoded reaction.
	 * @throws ZboziApiException
	 * @throws \Throwable
	 */
	public function addShopReviewReaction(int $reviewId, string $reaction) {
		if(!strlen($reaction) || strlen($reaction) > self::MAX_REACTION_LENGTH) {
			throw new \InvalidArgumentException(sprintf('Reaction length must be between 1 and %d', self::MAX_REACTION_LENGTH));
		}
		$response = $this->apiClient->sendRequest('PUT', '/v1/shop/reviews/' . $reviewId . '/reaction', json_encode([
			'reaction' => $reaction
		]));
		if($response->getStatusCode() !== 201) {
			CommonErrorsHandler::handleResponse($response);
		}
	}
}