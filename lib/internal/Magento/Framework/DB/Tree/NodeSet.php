<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Tree;

/**
 * TODO implements iterators
 *
 * @deprecated 102.0.0 Not used anymore.
 */
class NodeSet implements \Iterator, \Countable
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
     *
     * @deprecated 102.0.0
     */
    public function __construct()
    {
        $this->_nodes = [];
        $this->_current = 0;
        $this->_currentNode = 0;
        $this->count = 0;
    }

    /**
     * Adds a node to node list.
     *
     * @param Node $node
     * @return int
     *
     * @deprecated 102.0.0
     */
    public function addNode(Node $node)
    {
        $this->_nodes[$this->_currentNode] = $node;
        $this->count++;
        return ++$this->_currentNode;
    }

    /**
     * Retrieves count elements in node list.
     *
     * @return int
     *
     * @deprecated 102.0.0
     */
    #[\ReturnTypeWillChange]
    public function count()
    {
        return $this->count;
    }

    /**
     * Checks if current position is valid.
     *
     * @return bool
     *
     * @deprecated 102.0.0
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        return isset($this->_nodes[$this->_current]);
    }

    /**
     * Move forward to next element.
     *
     * @return false|int
     *
     * @deprecated 102.0.0
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        if ($this->_current > $this->_currentNode) {
            return false;
        } else {
            return $this->_current++;
        }
    }

    /**
     * Retrieves the key of the current element.
     *
     * @return int
     *
     * @deprecated 102.0.0
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->_current;
    }

    /**
     * Retrieves the current node.
     *
     * @return Node
     *
     * @deprecated 102.0.0
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->_nodes[$this->_current];
    }

    /**
     * Rewinds the Iterator to the first element.
     *
     * @return void
     *
     * @deprecated 102.0.0
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->_current = 0;
    }
}
