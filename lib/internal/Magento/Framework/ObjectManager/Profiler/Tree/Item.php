<?php
/**
 *
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
namespace Magento\Framework\ObjectManager\Profiler\Tree;

class Item
{
    /**
     * @var string
     */
    protected $class;

    /**
     * @var Item|null
     */
    protected $parent = null;

    /**
     * @var string
     */
    protected $hash = null;

    /**
     * @var array
     */
    protected $children = array();

    /**
     * @param string $class
     * @param Item $parent
     */
    public function __construct($class, Item $parent = null)
    {
        $this->class = $class;
        $this->parent = $parent;

        if ($parent) {
            $parent->addChild($this);
        }
    }

    /**
     * Retrieve class
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Add child
     *
     * @param Item $item
     * @return void
     */
    public function addChild(Item $item)
    {
        $this->children[] = $item;
    }

    /**
     * Retrieve list of children
     *
     * @return array[Item]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Retrieve parent
     *
     * @return Item|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set hash
     *
     * @param string $hash
     * @return void
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    /**
     * Retrieve hash
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }
}
