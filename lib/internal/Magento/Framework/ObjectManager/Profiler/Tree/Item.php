<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\Profiler\Tree;

/**
 * Class \Magento\Framework\ObjectManager\Profiler\Tree\Item
 *
 * @since 2.0.0
 */
class Item
{
    /**
     * @var string
     * @since 2.0.0
     */
    protected $class;

    /**
     * @var Item|null
     * @since 2.0.0
     */
    protected $parent = null;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $hash = null;

    /**
     * @var array
     * @since 2.0.0
     */
    protected $children = [];

    /**
     * @param string $class
     * @param Item $parent
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function addChild(Item $item)
    {
        $this->children[] = $item;
    }

    /**
     * Retrieve list of children
     *
     * @return array[Item]
     * @since 2.0.0
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Retrieve parent
     *
     * @return Item|null
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    /**
     * Retrieve hash
     *
     * @return string
     * @since 2.0.0
     */
    public function getHash()
    {
        return $this->hash;
    }
}
