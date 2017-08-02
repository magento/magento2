<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Tree;

/**
 * TODO implements iterators
 *
 * @since 2.0.0
 */
class NodeSet implements \Iterator
{
    /**
     * @var Node[]
     * @since 2.0.0
     */
    private $_nodes = [];

    /**
     * @var int
     * @since 2.0.0
     */
    private $_currentNode = 0;

    /**
     * @var int
     * @since 2.0.0
     */
    private $_current = 0;

    /**
     * Constructor
     * @since 2.0.0
     */
    public function __construct()
    {
        $this->_nodes = [];
        $this->_current = 0;
        $this->_currentNode = 0;
        $this->count = 0;
    }

    /**
     * @param Node $node
     * @return int
     * @since 2.0.0
     */
    public function addNode(Node $node)
    {
        $this->_nodes[$this->_currentNode] = $node;
        $this->count++;
        return ++$this->_currentNode;
    }

    /**
     * @return int
     * @since 2.0.0
     */
    public function count()
    {
        return $this->count;
    }

    /**
     * @return bool
     * @since 2.0.0
     */
    public function valid()
    {
        return isset($this->_nodes[$this->_current]);
    }

    /**
     * @return false|int
     * @since 2.0.0
     */
    public function next()
    {
        if ($this->_current > $this->_currentNode) {
            return false;
        } else {
            return $this->_current++;
        }
    }

    /**
     * @return int
     * @since 2.0.0
     */
    public function key()
    {
        return $this->_current;
    }

    /**
     * @return Node
     * @since 2.0.0
     */
    public function current()
    {
        return $this->_nodes[$this->_current];
    }

    /**
     * @return void
     * @since 2.0.0
     */
    public function rewind()
    {
        $this->_current = 0;
    }
}
