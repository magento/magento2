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
    private $_nodes = array();

    /**
     * @var int
     */
    private $_currentNode = 0;

    /**
     * @var int
     */
    private $_current = 0;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_nodes = array();
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
