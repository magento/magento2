<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
    protected $children = [];

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
