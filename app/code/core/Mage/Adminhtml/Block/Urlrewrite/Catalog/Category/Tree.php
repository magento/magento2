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
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Categories tree block for URL rewrites editing process
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Urlrewrite_Catalog_Category_Tree extends Mage_Adminhtml_Block_Catalog_Category_Abstract
{
    /**
     * List of allowed category ids
     *
     * @var array|null
     */
    protected $_allowedCategoryIds = null;

    protected $_template = 'urlrewrite/categories.phtml';

    /**
     * Get categories tree as recursive array
     *
     * @param int $parentId
     * @param bool $asJson
     * @param int $recursionLevel
     * @return array
     */
    public function getTreeArray($parentId = null, $asJson = false, $recursionLevel = 3)
    {
        $productId = Mage::app()->getRequest()->getParam('product');
        if ($productId) {
            $product = Mage::getModel('Mage_Catalog_Model_Product')->setId($productId);
            $this->_allowedCategoryIds = $product->getCategoryIds();
            unset($product);
        }

        $result = array();
        if ($parentId) {
            $category = Mage::getModel('Mage_Catalog_Model_Category')->load($parentId);
            if (!empty($category)) {
                $tree = $this->_getNodesArray($this->getNode($category, $recursionLevel));
                if (!empty($tree) && !empty($tree['children'])) {
                    $result = $tree['children'];
                }
            }
        } else {
            $result = $this->_getNodesArray($this->getRoot(null, $recursionLevel));
        }

        if ($asJson) {
            return Mage::helper('Mage_Core_Helper_Data')->jsonEncode($result);
        }

        $this->_allowedCategoryIds = null;

        return $result;
    }

    /**
     * Get categories collection
     *
     * @return Mage_Catalog_Model_Resource_Category_Collection
     */
    public function getCategoryCollection()
    {
        $collection = $this->_getData('category_collection');
        if (is_null($collection)) {
            $collection = Mage::getModel('Mage_Catalog_Model_Category')->getCollection()
                ->addAttributeToSelect(array('name', 'is_active'))
                ->setLoadProductCount(true);
            $this->setData('category_collection', $collection);
        }

        return $collection;
    }

    /**
     * Convert categories tree to array recursively
     *
     * @param  Varien_Data_Tree_Node $node
     * @return array
     */
    protected function _getNodesArray($node)
    {
        $result = array(
            'id'             => (int)$node->getId(),
            'parent_id'      => (int)$node->getParentId(),
            'children_count' => (int)$node->getChildrenCount(),
            'is_active'      => (bool)$node->getIsActive(),
            'name'           => $node->getName(),
            'level'          => (int)$node->getLevel(),
            'product_count'  => (int)$node->getProductCount()
        );

        if (is_array($this->_allowedCategoryIds) && !in_array($result['id'], $this->_allowedCategoryIds)) {
            $result['disabled'] = true;
        }

        if ($node->hasChildren()) {
            $result['children'] = array();
            foreach ($node->getChildren() as $childNode) {
                $result['children'][] = $this->_getNodesArray($childNode);
            }
        }
        $result['cls']      = ($result['is_active'] ? '' : 'no-') . 'active-category';
        $result['expanded'] = (!empty($result['children']));

        return $result;
    }

    /**
     * Get URL for categories tree ajax loader
     *
     * @return string
     */
    public function getLoadTreeUrl()
    {
        return Mage::helper('Mage_Adminhtml_Helper_Data')->getUrl('*/*/categoriesJson');
    }
}
