<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data;

use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Data\Tree\Node\Collection as NodeCollection;

/**
 * Data tree
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Tree
{
    /**
     * Nodes collection
     *
     * @var NodeCollection
     * @since 2.0.0
     */
    protected $_nodes;

    /**
     * Enter description here...
     *
     * @since 2.0.0
     */
    public function __construct()
    {
        $this->_nodes = new NodeCollection($this);
    }

    /**
     * Enter description here...
     *
     * @return \Magento\Framework\Data\Tree
     * @since 2.0.0
     */
    public function getTree()
    {
        return $this;
    }

    /**
     * Enter description here...
     *
     * @param Node $parentNode
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function load($parentNode = null)
    {
    }

    /**
     * Enter description here...
     *
     * @param int|string $nodeId
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function loadNode($nodeId)
    {
    }

    /**
     * Append child
     *
     * @param array|Node $data
     * @param Node $parentNode
     * @param Node $prevNode
     * @return Node
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function appendChild($data, $parentNode, $prevNode = null)
    {
        if (is_array($data)) {
            $node = $this->addNode(new Node($data, $parentNode->getIdField(), $this), $parentNode);
        } elseif ($data instanceof Node) {
            $node = $this->addNode($data, $parentNode);
        }
        return $node;
    }

    /**
     * Add node
     *
     * @param Node $node
     * @param Node $parent
     * @return Node
     * @since 2.0.0
     */
    public function addNode($node, $parent = null)
    {
        $this->_nodes->add($node);
        $node->setParent($parent);
        if ($parent !== null && $parent instanceof Node) {
            $parent->addChild($node);
        }
        return $node;
    }

    /**
     * Move node
     *
     * @param Node $node
     * @param Node $parentNode
     * @param Node $prevNode
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function moveNodeTo($node, $parentNode, $prevNode = null)
    {
    }

    /**
     * Copy node
     *
     * @param Node $node
     * @param Node $parentNode
     * @param Node $prevNode
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function copyNodeTo($node, $parentNode, $prevNode = null)
    {
    }

    /**
     * Remove node
     *
     * @param Node $node
     * @return $this
     * @since 2.0.0
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
     * Create node
     *
     * @param Node $parentNode
     * @param Node $prevNode
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function createNode($parentNode, $prevNode = null)
    {
    }

    /**
     * Get child
     *
     * @param Node $node
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function getChild($node)
    {
    }

    /**
     * Get children
     *
     * @param Node $node
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function getChildren($node)
    {
    }

    /**
     * Enter description here...
     *
     * @return NodeCollection
     * @since 2.0.0
     */
    public function getNodes()
    {
        return $this->_nodes;
    }

    /**
     * Enter description here...
     *
     * @param Node $nodeId
     * @return Node
     * @since 2.0.0
     */
    public function getNodeById($nodeId)
    {
        return $this->_nodes->searchById($nodeId);
    }

    /**
     * Get path
     *
     * @param Node $node
     * @return array
     * @since 2.0.0
     */
    public function getPath($node)
    {
        if ($node instanceof Node) {
        } elseif (is_numeric($node)) {
            if ($_node = $this->getNodeById($node)) {
                return $_node->getPath();
            }
        }
        return [];
    }
}
