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
 * @category   Magento
 * @package    Magento_Data
 * @copyright  Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Data tree
 *
 * @category   Magento
 * @package    Magento_Data
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Data;

class Tree
{

    /**
     * Nodes collection
     *
     * @var \Magento\Data\Tree\Node\Collection
     */
    protected $_nodes;

    /**
     * Enter description here...
     *
     */
    public function __construct()
    {
        $this->_nodes = new \Magento\Data\Tree\Node\Collection($this);
    }

    /**
     * Enter description here...
     *
     * @return \Magento\Data\Tree
     */
    public function getTree()
    {
        return $this;
    }

    /**
     * Enter description here...
     *
     * @param \Magento\Data\Tree\Node $parentNode
     */
    public function load($parentNode=null)
    {
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $nodeId
     */
    public function loadNode($nodeId)
    {
    }

    /**
     * Enter description here...
     *
     * @param array|\Magento\Data\Tree\Node $data
     * @param \Magento\Data\Tree\Node $parentNode
     * @param \Magento\Data\Tree\Node $prevNode
     * @return \Magento\Data\Tree\Node
     */
    public function appendChild($data=array(), $parentNode, $prevNode=null)
    {
        if (is_array($data)) {
            $node = $this->addNode(
                new \Magento\Data\Tree\Node($data, $parentNode->getIdField(), $this),
                $parentNode
            );
        } elseif ($data instanceof \Magento\Data\Tree\Node) {
            $node = $this->addNode($data, $parentNode);
        }
        return $node;
    }

    /**
     * Enter description here...
     *
     * @param \Magento\Data\Tree\Node $node
     * @param \Magento\Data\Tree\Node $parent
     * @return \Magento\Data\Tree\Node
     */
    public function addNode($node, $parent=null)
    {
        $this->_nodes->add($node);
        $node->setParent($parent);
        if (!is_null($parent) && ($parent instanceof \Magento\Data\Tree\Node) ) {
            $parent->addChild($node);
        }
        return $node;
    }

    /**
     * Enter description here...
     *
     * @param \Magento\Data\Tree\Node $node
     * @param \Magento\Data\Tree\Node $parentNode
     * @param \Magento\Data\Tree\Node $prevNode
     */
    public function moveNodeTo($node, $parentNode, $prevNode=null)
    {
    }

    /**
     * Enter description here...
     *
     * @param \Magento\Data\Tree\Node $node
     * @param \Magento\Data\Tree\Node $parentNode
     * @param \Magento\Data\Tree\Node $prevNode
     */
    public function copyNodeTo($node, $parentNode, $prevNode=null)
    {
    }

    /**
     * Enter description here...
     *
     * @param \Magento\Data\Tree\Node $node
     * @return \Magento\Data\Tree
     */
    public function removeNode($node)
    {
        $this->_nodes->delete($node);
        if ($node->getParent()) {
            $node->getParent()->removeChild($node);
        }
        unset($node);
        return $this;
    }

    /**
     * Enter description here...
     *
     * @param \Magento\Data\Tree\Node $parentNode
     * @param \Magento\Data\Tree\Node $prevNode
     */
    public function createNode($parentNode, $prevNode=null)
    {
    }

    /**
     * Enter description here...
     *
     * @param \Magento\Data\Tree\Node $node
     */
    public function getChild($node)
    {
    }

    /**
     * Enter description here...
     *
     * @param \Magento\Data\Tree\Node $node
     */
    public function getChildren($node)
    {
    }

    /**
     * Enter description here...
     *
     * @return \Magento\Data\Tree\Node\Collection
     */
    public function getNodes()
    {
        return $this->_nodes;
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $nodeId
     * @return \Magento\Data\Tree\Node
     */
    public function getNodeById($nodeId)
    {
        return $this->_nodes->searchById($nodeId);
    }

    /**
     * Enter description here...
     *
     * @param \Magento\Data\Tree\Node $node
     * @return array
     */
    public function getPath($node)
    {
        if ($node instanceof \Magento\Data\Tree\Node ) {

        } elseif (is_numeric($node)){
            if ($_node = $this->getNodeById($node)) {
                return $_node->getPath();
            }
        }
        return array();
    }

}
