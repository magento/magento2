<?php

/**
 * Copyright 2011 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Data\Tree;

use Magento\Framework\Data\Tree;
use Magento\Framework\Data\Tree\Node\Collection;

/**
 * Data tree node
 *
 * @api
 * @since 100.0.2
 */
class Node extends \Magento\Framework\DataObject
{
    /**
     * Parent node
     *
     * @var Node
     */
    protected $_parent;

    /**
     * Main tree object
     *
     * @var Tree
     */
    protected $_tree;

    /**
     * @var Collection
     */
    protected $_childNodes;

    /**
     * Node ID field name
     *
     * @var string
     */
    protected $_idField;

    /**
     * @param array $data
     * @param string $idField
     * @param Tree $tree
     * @param Node $parent
     */
    public function __construct($data, $idField, $tree, $parent = null)
    {
        $this->setTree($tree);
        $this->setParent($parent);
        $this->setIdField($idField);
        $this->setData($data);
        $this->_childNodes = new Collection($this);
    }

    /**
     * Retrieve node id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->getData($this->getIdField());
    }

    /**
     * Set node id field name
     *
     * @param   string $idField
     *
     * @return  $this
     */
    public function setIdField($idField)
    {
        $this->_idField = $idField;
        return $this;
    }

    /**
     * Retrieve node id field name
     *
     * @return string
     */
    public function getIdField()
    {
        return $this->_idField;
    }

    /**
     * Set node tree object
     *
     * @param   Tree $tree
     *
     * @return  $this
     */
    public function setTree(Tree $tree)
    {
        $this->_tree = $tree;
        return $this;
    }

    /**
     * Retrieve node tree object
     *
     * @return Tree
     */
    public function getTree()
    {
        return $this->_tree;
    }

    /**
     * Set node parent
     *
     * @param   Node $parent
     *
     * @return  $this
     */
    public function setParent($parent)
    {
        $this->_parent = $parent;
        return $this;
    }

    /**
     * Retrieve node parent
     *
     * @return Tree
     */
    public function getParent()
    {
        return $this->_parent;
    }

    /**
     * Check node children
     *
     * @return bool
     */
    public function hasChildren()
    {
        return $this->_childNodes->count() > 0;
    }

    /**
     * Set level
     *
     * @param mixed $level
     *
     * @return $this
     */
    public function setLevel($level)
    {
        $this->setData('level', $level);
        return $this;
    }

    /**
     * Set path ID
     *
     * @param mixed $path
     *
     * @return $this
     */
    public function setPathId($path)
    {
        $this->setData('path_id', $path);
        return $this;
    }

    /**
     * Seemingyly useless method
     *
     * @param Node $node
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @phpcs:disable Magento2.CodeAnalysis.EmptyBlock.DetectedFunction
     */
    public function isChildOf($node)
    {
    }

    /**
     * Load node children
     *
     * @param   int  $recursionLevel
     *
     * @return  \Magento\Framework\Data\Tree\Node
     */
    public function loadChildren($recursionLevel = 0)
    {
        $this->_tree->load($this, $recursionLevel);
        return $this;
    }

    /**
     * Retrieve node children collection
     *
     * @return Collection
     */
    public function getChildren()
    {
        return $this->_childNodes;
    }

    /**
     * Get all child nodes
     *
     * @param array $nodes
     *
     * @return array
     */
    public function getAllChildNodes(&$nodes = [])
    {
        foreach ($this->_childNodes as $node) {
            $nodes[$node->getId()] = $node;
            $node->getAllChildNodes($nodes);
        }
        return $nodes;
    }

    /**
     * Get last child
     *
     * @return mixed
     */
    public function getLastChild()
    {
        return $this->_childNodes->lastNode();
    }

    /**
     * Add child node
     *
     * @param   Node $node
     *
     * @return  Node
     */
    public function addChild($node)
    {
        $this->_childNodes->add($node);
        return $this;
    }

    /**
     * Append child
     *
     * @param Node $prevNode
     *
     * @return $this
     */
    public function appendChild($prevNode = null)
    {
        $this->_tree->appendChild($this, $prevNode);
        return $this;
    }

    /**
     * Move to
     *
     * @param Node $parentNode
     * @param Node $prevNode
     *
     * @return $this
     */
    public function moveTo($parentNode, $prevNode = null)
    {
        $this->_tree->moveNodeTo($this, $parentNode, $prevNode);
        return $this;
    }

    /**
     * Copy to
     *
     * @param Node $parentNode
     * @param Node $prevNode
     *
     * @return $this
     */
    public function copyTo($parentNode, $prevNode = null)
    {
        $this->_tree->copyNodeTo($this, $parentNode, $prevNode);
        return $this;
    }

    /**
     * Remove child
     *
     * @param Node $childNode
     *
     * @return $this
     */
    public function removeChild($childNode)
    {
        $this->_childNodes->delete($childNode);
        return $this;
    }

    /**
     * Get path
     *
     * @param array $prevNodes
     *
     * @return array
     */
    public function getPath(&$prevNodes = [])
    {
        if ($this->_parent) {
            $prevNodes[] = $this;
            $this->_parent->getPath($prevNodes);
        }

        return $prevNodes;
    }

    /**
     * Get is active
     *
     * @return mixed
     */
    public function getIsActive()
    {
        return $this->_getData('is_active');
    }

    /**
     * Get name
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->_getData('name');
    }
}
