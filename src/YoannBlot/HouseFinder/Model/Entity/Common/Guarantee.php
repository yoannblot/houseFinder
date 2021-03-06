<?php
declare(strict_types=1);

namespace YoannBlot\HouseFinder\Model\Entity\Common;

/**
 * Trait Guarantee.
 *
 * @package YoannBlot\HouseFinder\Model\Entity\Common
 */
trait Guarantee
{

    /**
     * @var float guarantee.
     */
    private $guarantee = 0;

    /**
     * @return float
     */
    public function getGuarantee(): ?float
    {
        return floatval($this->guarantee);
    }

    /**
     * @param float $fGuarantee
     */
    public function setGuarantee(float $fGuarantee): void
    {
        if ($fGuarantee < 0 || $fGuarantee > 10000) {
            $fGuarantee = 0;
        }
        $this->guarantee = $fGuarantee;
    }
}