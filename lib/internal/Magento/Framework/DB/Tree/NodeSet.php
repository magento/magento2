<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Tree;

/**
 * TODO implements iterators
 *
 */
class NodeSet implements \Iterator
{
    /**
     * @var Node[]
     */
    private $_nodes;

    /**
     * @var int
     */
    private $_current;

    /**
     * @var int
     */
    private $_currentNode;

    /**
     * @var int
     */
    private $count;

    /**
     * Constructor
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
     */
    public function addNode(Node $node)
    {
        $this->_nodes[$this->_currentNode] = $node;
        $this->count++;
        return ++$this->_currentNode;
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->count;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return isset($this->_nodes[$this->_current]);
    }

    /**
     * @return false|int
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
     */
    public function key()
    {
        return $this->_current;
    }

    /**
     * @return Node
     */
    public function current()
    {
        return $this->_nodes[$this->_current];
    }

    /**
     * @return void
     */
    public function rewind()
    {
        $this->_current = 0;
    }
}
