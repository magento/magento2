<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Reports\Model;

class Item extends \Magento\Framework\Object
{
    /**
     * @var bool
     */
    protected $_isEmpty = false;

    /**
     * @var array
     */
    protected $_children = array();

    /**
     * Set is empty indicator
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
     *
     * @return bool
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
     *
     * @return array
     */
    public function getChildren()
    {
        return $this->_children;
    }

    /**
     * Set children
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
