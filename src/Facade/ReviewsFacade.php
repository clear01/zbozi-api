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

class ReviewsFacade
{
	const MAX_REACTION_LENGTH = 10000;

	/** Max page size of GET /nakupy/reviews/ */
	const SHOP_REVIEWS_PAGE_LIMIT = 100;

	/** Max page size of GET /nakupy/product-reviews/ */
	const PRODUCT_REVIEWS_LIMIT = 1000;

	/** GET /nakupy/product-reviews/ accepts at most 180 days between fromDatetime and toDatetime */
	const PRODUCT_REVIEWS_MAX_WINDOW = 179 * 86400;

	/** Full product reviews window is split until it is shorter than this [s] */
	const PRODUCT_REVIEWS_MIN_WINDOW = 60;

	/** @var ApiClient */
	protected $apiClient;

	public function __construct(ApiClient $apiClient)
	{
		$this->apiClient = $apiClient;
	}

	/**
	 * Returns product reviews created since $fromDate. The period is split into
	 * windows accepted by the API; a window that hits the page limit is halved.
	 *
	 * @param int|null $limit max number of returned reviews, null for all
	 * @return ProductReview[]
	 * @throws ZboziApiException
	 * @throws \Throwable
	 */
	public function getProductReviews(\DateTimeInterface $fromDate, ?int $limit): array {
		$from = $fromDate->getTimestamp();
		$to = time();

		/** @var ProductReview[] $productReviews indexed by review ID to drop duplicates on window borders */
		$productReviews = [];
		while($from < $to) {
			$windowTo = min($from + self::PRODUCT_REVIEWS_MAX_WINDOW, $to);
			foreach($this->getProductReviewsInWindow($from, $windowTo) as $productReview) {
				$productReviews[$productReview->getProductReviewId()] = $productReview;
				if($limit && count($productReviews) >= $limit) {
					return array_values($productReviews);
				}
			}
			$from = $windowTo;
		}
		return array_values($productReviews);
	}

	/**
	 * @return ProductReview[]
	 * @throws ZboziApiException
	 */
	protected function getProductReviewsInWindow(int $from, int $to): array {
		$response = $this->apiClient->sendRequest('GET', '/nakupy/product-reviews/', null, [
			'fromDatetime' => $this->formatDateTime($from),
			'toDatetime' => $this->formatDateTime($to),
			'limit' => self::PRODUCT_REVIEWS_LIMIT,
		]);
		if($response->getStatusCode() !== 200) {
			CommonErrorsHandler::handleResponse($response);
		}

		$data = ContentParser::parseBody((string) $response->getBody());
		if(!isset($data['items']) || !is_array($data['items'])) {
			throw new ZboziApiException('Invalid response');
		}

		// the endpoint has no paging – a full page may mean some reviews were cut off
		if(count($data['items']) >= self::PRODUCT_REVIEWS_LIMIT && $to - $from > self::PRODUCT_REVIEWS_MIN_WINDOW) {
			$middle = (int) (($from + $to) / 2);
			return array_merge(
				$this->getProductReviewsInWindow($from, $middle),
				$this->getProductReviewsInWindow($middle, $to)
			);
		}

		$productReviews = [];
		foreach($data['items'] as $record) {
			$productReviews[] = ProductReviewMapper::buildFromFlatData($record);
		}
		return $productReviews;
	}

	/**
	 * When neither $limit nor $offset is given, all reviews in the period are returned (all pages are loaded).
	 *
	 * @return ShopReview[]
	 * @throws ZboziApiException
	 * @throws \Throwable
	 */
	public function getShopReviews(\DateTimeInterface $fromDate, ?\DateTimeInterface $toDate, ?int $limit, ?int $offset): array {
		$query = [
			'fromDatetime' => $this->formatDateTime($fromDate->getTimestamp()),
			// fixed upper bound keeps offsets stable while paging
			'toDatetime' => $this->formatDateTime($toDate ? $toDate->getTimestamp() : time()),
		];

		if($limit !== null || $offset !== null) {
			if($limit) {
				$query['limit'] = $limit;
			}
			if($offset) {
				$query['offset'] = $offset;
			}
			return $this->getShopReviewPage($query)[0];
		}

		$shopReviews = [];
		$query['limit'] = self::SHOP_REVIEWS_PAGE_LIMIT;
		$query['offset'] = 0;
		do {
			list($page, $count) = $this->getShopReviewPage($query);
			$shopReviews = array_merge($shopReviews, $page);
			$query['offset'] += self::SHOP_REVIEWS_PAGE_LIMIT;
		} while(count($page) && $query['offset'] < $count);

		return $shopReviews;
	}

	/**
	 * @return array [ShopReview[], int total count]
	 * @throws ZboziApiException
	 */
	protected function getShopReviewPage(array $query): array {
		$response = $this->apiClient->sendRequest('GET', '/nakupy/reviews/', null, $query);
		if($response->getStatusCode() !== 200) {
			CommonErrorsHandler::handleResponse($response);
		}

		$data = ContentParser::parseBody((string) $response->getBody());
		if(!isset($data['items']) || !is_array($data['items'])) {
			throw new ZboziApiException('Invalid response');
		}

		$shopReviews = [];
		foreach($data['items'] as $record) {
			$shopReviews[] = ShopReviewMapper::buildFromFlatData($record);
		}
		return [$shopReviews, (int) ($data['meta']['count'] ?? count($shopReviews))];
	}

	/**
	 * @throws ZboziApiException
	 * @throws \Throwable
	 */
	public function getShopReviewById(int $reviewId): ?ShopReview {
		$response = $this->apiClient->sendRequest('GET', '/nakupy/reviews/' . $reviewId);
		if($response->getStatusCode() === 404) {
			return null;
		}
		if($response->getStatusCode() !== 200) {
			CommonErrorsHandler::handleResponse($response);
		}
		return ShopReviewMapper::buildFromFlatData(ContentParser::parseBody((string) $response->getBody()));
	}

	/**
	 * @param int $reviewId
	 * @param string $reaction UTF-8 encoded reaction.
	 * @throws ZboziApiException
	 * @throws \Throwable
	 */
	public function addShopReviewReaction(int $reviewId, string $reaction) {
		if(!strlen($reaction) || mb_strlen($reaction, 'UTF-8') > self::MAX_REACTION_LENGTH) {
			throw new \InvalidArgumentException(sprintf('Reaction length must be between 1 and %d', self::MAX_REACTION_LENGTH));
		}
		$response = $this->apiClient->sendRequest('PUT', '/nakupy/reviews/' . $reviewId . '/reaction', json_encode([
			'reaction' => $reaction
		]));
		if($response->getStatusCode() !== 201) {
			CommonErrorsHandler::handleResponse($response);
		}
	}

	private function formatDateTime(int $timestamp): string {
		return gmdate('Y-m-d\TH:i:s\Z', $timestamp);
	}
}
