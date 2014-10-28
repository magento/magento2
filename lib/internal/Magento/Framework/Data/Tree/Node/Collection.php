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
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tree node collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Data\Tree\Node;

use Magento\Framework\Data\Tree;
use Magento\Framework\Data\Tree\Node;

class Collection implements \ArrayAccess, \IteratorAggregate
{
    /**
     * @var array
     */
    private $_nodes;

    /**
     * @var Node
     */
    private $_container;

    /**
     * @param Node $container
     */
    public function __construct($container)
    {
        $this->_nodes = array();
        $this->_container = $container;
    }

    /**
     * Get the nodes
     *
     * @return array
     */
    public function getNodes()
    {
        return $this->_nodes;
    }

    /**
     * Implementation of \IteratorAggregate::getIterator()
     *
     * @return \ArrayIterator
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
     */
    public function offsetSet($key, $value)
    {
        $this->_nodes[$key] = $value;
    }

    /**
     * Implementation of \ArrayAccess:offsetGet()
     * @param string $key
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->_nodes[$key];
    }

    /**
     * Implementation of \ArrayAccess:offsetUnset()
     * @param string $key
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->_nodes[$key]);
    }

    /**
     * Implementation of \ArrayAccess:offsetExists()
     * @param string $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return isset($this->_nodes[$key]);
    }

    /**
     * Adds a node to this node
     * @param Node $node
     * @return Node
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
     */
    public function count()
    {
        return count($this->_nodes);
    }

    /**
     * Return the last node
     *
     * @return mixed
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
     */
    public function searchById($nodeId)
    {
        if (isset($this->_nodes[$nodeId])) {
            return $this->_nodes[$nodeId];
        }
        return null;
    }
}
