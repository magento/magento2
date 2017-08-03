<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Categories tree with checkboxes
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Category\Checkboxes;

use Magento\Framework\Data\Tree\Node;

/**
 * Class \Magento\Catalog\Block\Adminhtml\Category\Checkboxes\Tree
 *
 * @since 2.0.0
 */
class Tree extends \Magento\Catalog\Block\Adminhtml\Category\Tree
{
    /**
     * @var int[]
     * @since 2.0.0
     */
    protected $_selectedIds = [];

    /**
     * @var array
     * @since 2.0.0
     */
    protected $_expandedPath = [];

    /**
     * @return void
     * @since 2.0.0
     */
    protected function _prepareLayout()
    {
        $this->setTemplate('catalog/category/checkboxes/tree.phtml');
    }

    /**
     * @return int[]
     * @since 2.0.0
     */
    public function getCategoryIds()
    {
        return $this->_selectedIds;
    }

    /**
     * @param mixed $ids
     * @return $this
     * @since 2.0.0
     */
    public function setCategoryIds($ids)
    {
        if (empty($ids)) {
            $ids = [];
        } elseif (!is_array($ids)) {
            $ids = [(int)$ids];
        }
        $this->_selectedIds = $ids;
        return $this;
    }

    /**
     * @return array
     * @since 2.0.0
     */
    protected function getExpandedPath()
    {
        return $this->_expandedPath;
    }

    /**
     * @param string $path
     * @return $this
     * @since 2.0.0
     */
    protected function setExpandedPath($path)
    {
        $this->_expandedPath = array_merge($this->_expandedPath, explode('/', $path));
        return $this;
    }

    /**
     * @param array|Node $node
     * @param int $level
     * @return array
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @since 2.0.0
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
}
