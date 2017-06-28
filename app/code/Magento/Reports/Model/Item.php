<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Model;

/**
 * @api
 */
class Item extends \Magento\Framework\DataObject
{
    /**
     * @var bool
     */
    protected $_isEmpty = false;

    /**
     * @var array
     */
    protected $_children = [];

    /**
     * Set is empty indicator
     * @codeCoverageIgnore
     *
     * @param bool $flag
     * @return $this
     */
    public function setIsEmpty($flag = true)
    {
        $this->_isEmpty = $flag;
        return $this;
    }

    /**
     * Get is empty indicator
     * @codeCoverageIgnore
     *
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsEmpty()
    {
        return $this->_isEmpty;
    }

    /**
     * @return void
     */
    public function hasIsEmpty()
    {
    }

    /**
     * Get children
     * @codeCoverageIgnore
     *
     * @return array
     */
    public function getChildren()
    {
        return $this->_children;
    }

    /**
     * Set children
     * @codeCoverageIgnore
     *
     * @param array $children
     * @return $this
     */
    public function setChildren($children)
    {
        $this->_children = $children;
        return $this;
    }

    /**
     * Indicator of whether or not children are present
     *
     * @return bool
     */
    public function hasChildren()
    {
        return count($this->_children) > 0 ? true : false;
    }

    /**
     * Add child to array of items
     *
     * @param array $child
     * @return $this
     */
    public function addChild($child)
    {
        $this->_children[] = $child;
        return $this;
    }
}
