<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Reflection\Test\Unit;

/**
 * Dummy data object to be used by TypeProcessorTest
 */
class DataObject
{
    /**
     * @var string
     */
    protected $attrName;

    /**
     * @var bool
     */
    protected $isActive;

    /**
     * @var string
     */
    private $name;

    /**
     * @return string
     */
    public function getAttrName()
    {
        return $this->attrName;
    }

    /**
     * @param string $attrName
     * @return $this
     */
    public function setAttrName($attrName)
    {
        $this->attrName = $attrName;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     * @return $this
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * @param null|string $name Name of the attribute
     * @return $this
     */
    public function setName($name = null)
    {
        $this->name = $name;
        return $this;
    }
}
