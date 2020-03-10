<?php
declare(strict_types = 1);

namespace Clear01\ZboziApi\Model;

class ProductReview
{
	/** @var \DateTimeInterface */
	protected $dateCreated;

	/** @var \DateTimeInterface|null */
	protected $dateLastModified;

	/** @var string */
	protected $negativeComments;

	/** @var string */
	protected $positiveComments;

	/** @var ProductData */
	protected $productData;

	/** @var string */
	protected $productReviewId;

	/** @var int|null 1 - 5 */
	protected $ratingStars;

	/** @var ReviewState */
	protected $state;

	/** @var string|null */
	protected $summary;

	/** @var string|null */
	protected $userName;

	public function __construct(
		\DateTimeInterface $dateCreated,
		?\DateTimeInterface $dateLastModified,
		string $negativeComments,
		string $positiveComments,
		ProductData $productData,
		string $productReviewId,
		?int $ratingStars,
		ReviewState $state,
		?string $summary,
		?string $userName
	) {
		$this->dateCreated = $dateCreated;
		$this->dateLastModified = $dateLastModified;
		$this->negativeComments = $negativeComments;
		$this->positiveComments = $positiveComments;
		$this->productReviewId = $productReviewId;
		$this->ratingStars = $ratingStars;
		$this->state = $state;
		$this->summary = $summary;
		$this->userName = $userName;
		$this->productData = $productData;
	}

	public function getDateCreated(): \DateTimeInterface
	{
		return $this->dateCreated;
	}

	public function getDateLastModified(): ?\DateTimeInterface
	{
		return $this->dateLastModified;
	}

	public function getNegativeComments(): string
	{
		return $this->negativeComments;
	}

	public function getPositiveComments(): string
	{
		return $this->positiveComments;
	}

	public function getProductReviewId(): string
	{
		return $this->productReviewId;
	}

	public function getRatingStars(): ?int
	{
		return $this->ratingStars;
	}

	public function getState(): ReviewState
	{
		return $this->state;
	}

	public function getSummary(): ?string
	{
		return $this->summary;
	}

	public function getUserName(): ?string
	{
		return $this->userName;
	}

	public function getProductData(): ProductData
	{
		return $this->productData;
	}

}