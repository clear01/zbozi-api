<?php
declare(strict_types = 1);

namespace Clear01\ZboziApi\Model;

class Satisfaction
{
	/** @var int|null 0 - 1 */
	protected $communication;

	/** @var int|null 0 - 1 */
	protected $deliveryDate;

	/** @var int|null 0 - 1 */
	protected $deliveryQuality;

	/** @var int 0 - 2 */
	protected $overall;

	public function __construct(?int $communication, ?int $deliveryDate, ?int $deliveryQuality, int $overall)
	{
		$this->communication = $communication;
		$this->deliveryDate = $deliveryDate;
		$this->deliveryQuality = $deliveryQuality;
		$this->overall = $overall;
	}

	/** @return int|null 0 - 1 */
	public function getCommunication(): ?int
	{
		return $this->communication;
	}

	/** @return int|null 0 - 1 */
	public function getDeliveryDate(): ?int
	{
		return $this->deliveryDate;
	}

	/** @return int|null 0 - 1 */
	public function getDeliveryQuality(): ?int
	{
		return $this->deliveryQuality;
	}

	/** @return int 0 - 2 */
	public function getOverall(): int
	{
		return $this->overall;
	}

}