<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tree node collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Data\Tree\Node;

use Magento\Framework\Data\Tree;
use Magento\Framework\Data\Tree\Node;

/**
 * @api
 * @since 2.0.0
 */
class Collection implements \ArrayAccess, \IteratorAggregate
{
    /**
     * @var array
     * @since 2.0.0
     */
    private $_nodes;

    /**
     * @var Node
     * @since 2.0.0
     */
    private $_container;

    /**
     * @param Node $container
     * @since 2.0.0
     */
    public function __construct($container)
    {
        $this->_nodes = [];
        $this->_container = $container;
    }

    /**
     * Get the nodes
     *
     * @return array
     * @since 2.0.0
     */
    public function getNodes()
    {
        return $this->_nodes;
    }

    /**
     * Implementation of \IteratorAggregate::getIterator()
     *
     * @return \ArrayIterator
     * @since 2.0.0
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->_nodes);
    }

    /**
     * Implementation of \ArrayAccess:offsetSet()
     *
     * @param string $key
     * @param mixed $value
     * @return void
     * @since 2.0.0
     */
    public function offsetSet($key, $value)
    {
        $this->_nodes[$key] = $value;
    }

    /**
     * Implementation of \ArrayAccess:offsetGet()
     * @param string $key
     * @return mixed
     * @since 2.0.0
     */
    public function offsetGet($key)
    {
        return $this->_nodes[$key];
    }

    /**
     * Implementation of \ArrayAccess:offsetUnset()
     * @param string $key
     * @return void
     * @since 2.0.0
     */
    public function offsetUnset($key)
    {
        unset($this->_nodes[$key]);
    }

    /**
     * Implementation of \ArrayAccess:offsetExists()
     * @param string $key
     * @return bool
     * @since 2.0.0
     */
    public function offsetExists($key)
    {
        return isset($this->_nodes[$key]);
    }

    /**
     * Adds a node to this node
     * @param Node $node
     * @return Node
     * @since 2.0.0
     */
    public function add(Node $node)
    {
        $node->setParent($this->_container);

        // Set the Tree for the node
        if ($this->_container->getTree() instanceof Tree) {
            $node->setTree($this->_container->getTree());
        }

        $this->_nodes[$node->getId()] = $node;

        return $node;
    }

    /**
     * Delete
     *
     * @param Node $node
     * @return $this
     * @since 2.0.0
     */
    public function delete($node)
    {
        if (isset($this->_nodes[$node->getId()])) {
            unset($this->_nodes[$node->getId()]);
        }
        return $this;
    }

    /**
     * Return count
     *
     * @return int
     * @since 2.0.0
     */
    public function count()
    {
        return count($this->_nodes);
    }

    /**
     * Return the last node
     *
     * @return mixed
     * @since 2.0.0
     */
    public function lastNode()
    {
        if (!empty($this->_nodes)) {
            $result = end($this->_nodes);
            reset($this->_nodes);
        } else {
            $result = null;
        }

        return $result;
    }

    /**
     * Search by Id
     *
     * @param string $nodeId
     * @return null
     * @since 2.0.0
     */
    public function searchById($nodeId)
    {
        if (isset($this->_nodes[$nodeId])) {
            return $this->_nodes[$nodeId];
        }
        return null;
    }
}
