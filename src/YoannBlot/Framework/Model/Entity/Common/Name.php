<?php

namespace YoannBlot\Framework\Model\Entity\Common;

/**
 * Trait Name.
 *
 * @package YoannBlot\Framework\Model\Entity\Common
 */
trait Name {

    /**
     * @var string name.
     */
    private $name = '';

    /**
     * @return string name.
     */
    public function getName (): string {
        return $this->name;
    }

    /**
     * @param string $sName new name.
     */
    public function setName (string $sName) {
        if (strlen($sName) > 2) {
            $this->name = $sName;
        }
    }
}