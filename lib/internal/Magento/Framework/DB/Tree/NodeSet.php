<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\DB\Tree;

/**
 * TODO implements iterators
 *
 * @deprecated Not used anymore.
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
     * @deprecated
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
     *
     * @deprecated
     */
    public function addNode(Node $node)
    {
        $this->_nodes[$this->_currentNode] = $node;
        $this->count++;
        return ++$this->_currentNode;
    }

    /**
     * @return int
     *
     * @deprecated
     */
    public function count()
    {
        return $this->count;
    }

    /**
     * @return bool
     *
     * @deprecated
     */
    public function valid()
    {
        return isset($this->_nodes[$this->_current]);
    }

    /**
     * @return false|int
     *
     * @deprecated
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
     *
     * @deprecated
     */
    public function key()
    {
        return $this->_current;
    }

    /**
     * @return Node
     *
     * @deprecated
     */
    public function current()
    {
        return $this->_nodes[$this->_current];
    }

    /**
     * @return void
     *
     * @deprecated
     */
    public function rewind()
    {
        $this->_current = 0;
    }
}
