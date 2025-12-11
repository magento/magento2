<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

/**
 * Categories tree with checkboxes
 */
namespace Magento\Catalog\Block\Adminhtml\Category\Checkboxes;

use Magento\Catalog\Block\Adminhtml\Category\Tree as CategoryTree;
use Magento\Framework\Data\Tree\Node;

class Tree extends CategoryTree
{
    /**
     * @var int[]
     */
    protected $_selectedIds = [];

    /**
     * @var array
     */
    protected $_expandedPath = [];

    /**
     * Method to prepare layout.
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        $this->setTemplate('Magento_Catalog::catalog/category/checkboxes/tree.phtml');
    }

    /**
     * Method to get category ids.
     *
     * @return int[]
     */
    public function getCategoryIds()
    {
        return $this->_selectedIds;
    }

    /**
     * Method to set category ids.
     *
     * @param mixed $ids
     * @return $this
     */
    public function setCategoryIds($ids)
    {
        if (empty($ids)) {
            $ids = [];
        } elseif (!is_array($ids)) {
            $ids = [(int)$ids];
        }
        $this->_selectedIds = $ids;

        // Pre-compute expanded paths for all selected categories so that
        // ancestors are marked as expanded before the JSON is generated.
        $this->_expandedPath = [];
        if (!empty($ids)) {
            $collection = $this->_categoryFactory->create()->getCollection();
            $collection->addAttributeToSelect('path')
                ->addAttributeToFilter('entity_id', ['in' => $ids]);
            foreach ($collection as $category) {
                $this->setExpandedPath($category->getPath());
            }
        }
        return $this;
    }

    /**
     * Method to get expanded path.
     *
     * @return array
     */
    protected function getExpandedPath()
    {
        return $this->_expandedPath;
    }

    /**
     * Method to set expanded path.
     *
     * @param string $path
     * @return $this
     */
    protected function setExpandedPath($path)
    {
        $this->_expandedPath = array_merge($this->_expandedPath, explode('/', $path ?: ''));
        return $this;
    }

    /**
     * Method to get node json.
     *
     * @param array|Node $node
     * @param int $level
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _getNodeJson($node, $level = 1)
    {
        $item = [];
        $item['text'] = $this->escapeHtml($node->getName());
        if ($this->_withProductCount) {
            $item['text'] .= ' (' . $node->getProductCount() . ')';
        }
        $item['id'] = $node->getId();
        $item['path'] = $node->getData('path');
        $item['cls'] = 'folder ' . ($node->getIsActive() ? 'active-category' : 'no-active-category');
        $item['allowDrop'] = false;
        $item['allowDrag'] = false;
        if (in_array($node->getId(), $this->getCategoryIds())) {
            $this->setExpandedPath($node->getData('path'));
            $item['checked'] = true;
        }
        if ($node->getLevel() < 2) {
            $this->setExpandedPath($node->getData('path'));
        }
        if ($node->hasChildren()) {
            $item['children'] = [];
            foreach ($node->getChildren() as $child) {
                $item['children'][] = $this->_getNodeJson($child, $level + 1);
            }
        }
        if (empty($item['children']) && (int)$node->getChildrenCount() > 0) {
            $item['children'] = [];
        }
        $item['expanded'] = in_array($node->getId(), $this->getExpandedPath());
        return $item;
    }

    /**
     * Get tree structure
     *
     * Ensure that deeply selected categories are present by building the tree
     * around selected IDs rather than the default 3-level root-only tree.
     *
     * @param mixed|null $parenNodeCategory
     * @return array
     */
    public function getTree($parenNodeCategory = null)
    {
        // For AJAX child loads, respect the requested parent node
        if ($parenNodeCategory !== null) {
            $root = $this->getRoot($parenNodeCategory);
        } else {
            $root = empty($this->_selectedIds)
                ? $this->getRoot($parenNodeCategory)
                : $this->getRootByIds($this->_selectedIds);
        }

        $rootArray = $this->_getNodeJson($root);
        return $rootArray['children'] ?? [];
    }

    /**
     * Get tree json
     *
     * Ensure that deeply selected categories are present in the JSON output.
     *
     * @param mixed|null $parenNodeCategory
     * @return string
     */
    public function getTreeJson($parenNodeCategory = null)
    {
        // For AJAX child loads, respect the requested parent node
        if ($parenNodeCategory !== null) {
            $root = $this->getRoot($parenNodeCategory);
        } else {
            $root = empty($this->_selectedIds)
                ? $this->getRoot($parenNodeCategory)
                : $this->getRootByIds($this->_selectedIds);
        }

        $rootArray = $this->_getNodeJson($root);
        return $this->_jsonEncoder->encode($rootArray['children'] ?? []);
    }

    /**
     * Override parent's implementation to avoid using cached registry root
     *
     * @param array $ids
     * @return \Magento\Framework\Data\Tree\Node|array|null
     */
    public function getRootByIds($ids)
    {
        $ids = $this->_categoryTree->getExistingCategoryIdsBySpecifiedIds($ids);
        $tree = $this->_categoryTree->loadByIds($ids);
        $rootId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;
        $root = $tree->getNodeById($rootId);
        if ($root) {
            $root->setIsVisible(true);
            if ($root->getId() == \Magento\Catalog\Model\Category::TREE_ROOT_ID) {
                $root->setName(__('Root'));
            }
        }
        $tree->addCollectionData($this->getCategoryCollection());
        return $root;
    }
}
