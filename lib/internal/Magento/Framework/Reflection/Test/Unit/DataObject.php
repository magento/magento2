<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Reflection\Test\Unit;

use Magento\Framework\Reflection\Test\Unit\Fixture\TSampleInterface;

/**
 * Dummy data object to be used by TypeProcessorTest
 */
class DataObject
{
    /**
     * @var string
     */
    private $attrName;

    /**
     * @var bool
     */
    private $isActive;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $data = [];

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

    /**
     * @param string $key Key is used as index
     * @param string $value
     * @return void
     */
    public function setData(string $key, string $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * @param array $data
     * @return void
     */
    public function addData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @param TSampleInterface[] $list
     * @return void
     */
    public function addObjectList(array $list)
    {
        $this->data['objects'] = $list;
    }
}
