<?php
declare(strict_types=1);

namespace YoannBlot\HouseFinder\Model\Entity\Common;

/**
 * Trait Rent.
 *
 * @package YoannBlot\HouseFinder\Model\Entity\Common
 */
trait Rent
{

    /**
     * @var float rent.
     */
    private $rent = 0;

    /**
     * @return float
     */
    public function getRent(): float
    {
        return floatval($this->rent);
    }

    /**
     * @param float $fRent
     */
    public function setRent(float $fRent): void
    {
        if ($fRent < 0 || $fRent > 5000) {
            $fRent = 0;
        }
        $this->rent = $fRent;
    }
}