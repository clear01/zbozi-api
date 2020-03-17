<?php
declare(strict_types = 1);

namespace Clear01\ZboziApi\Model;

class ShopReview
{
	/** @var \DateTimeInterface */
	protected $dateCreated;

	/** @var string */
	protected $negativeComment;

	/** @var string */
	protected $positiveComment;

	/** @var string|null */
	protected $orderId;

	/** @var Satisfaction */
	protected $satisfaction;

	/** @var string|null */
	protected $shopReaction;

	/** @var ShopReactionState|null */
	protected $shopReactionState;

	/** @var integer */
	protected $shopReviewId;

	/** @var ReviewState */
	protected $state;

	/** @var string|null */
	protected $userName;

	public function __construct(
		\DateTimeInterface $dateCreated,
		string $negativeComment,
		string $positiveComment,
		?string $orderId,
		Satisfaction $satisfaction,
		?string $shopReaction,
		?ShopReactionState $shopReactionState,
		int $shopReviewId,
		ReviewState $state,
		?string $userName
	) {
		$this->dateCreated = $dateCreated;
		$this->negativeComment = $negativeComment;
		$this->positiveComment = $positiveComment;
		$this->orderId = $orderId;
		$this->satisfaction = $satisfaction;
		$this->shopReaction = $shopReaction;
		$this->shopReactionState = $shopReactionState;
		$this->shopReviewId = $shopReviewId;
		$this->state = $state;
		$this->userName = $userName;
	}

	public function getDateCreated(): \DateTimeInterface
	{
		return $this->dateCreated;
	}

	public function getNegativeComment(): string
	{
		return $this->negativeComment;
	}

	public function getPositiveComment(): string
	{
		return $this->positiveComment;
	}

	public function getOrderId(): ?string
	{
		return $this->orderId;
	}

	public function getSatisfaction(): Satisfaction
	{
		return $this->satisfaction;
	}

	public function getShopReaction(): ?string
	{
		return $this->shopReaction;
	}

	public function getShopReactionState(): ?ShopReactionState
	{
		return $this->shopReactionState;
	}

	public function getShopReviewId(): int
	{
		return $this->shopReviewId;
	}

	public function getState(): ReviewState
	{
		return $this->state;
	}

	public function getUserName(): ?string
	{
		return $this->userName;
	}

}