<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Model;

/**
 * @api
 * @since 2.0.0
 */
class Item extends \Magento\Framework\DataObject
{
    /**
     * @var bool
     * @since 2.0.0
     */
    protected $_isEmpty = false;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $_children = [];

    /**
     * Set is empty indicator
     * @codeCoverageIgnore
     *
     * @param bool $flag
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getIsEmpty()
    {
        return $this->_isEmpty;
    }

    /**
     * @return void
     * @since 2.0.0
     */
    public function hasIsEmpty()
    {
    }

    /**
     * Get children
     * @codeCoverageIgnore
     *
     * @return array
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function addChild($child)
    {
        $this->_children[] = $child;
        return $this;
    }
}
