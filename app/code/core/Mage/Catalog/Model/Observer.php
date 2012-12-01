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
 * @package     Mage_Catalog
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog Observer
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Observer
{
    /**
     * Process catalog ata related with store data changes
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_Catalog_Model_Observer
     */
    public function storeEdit(Varien_Event_Observer $observer)
    {
        /** @var $store Mage_Core_Model_Store */
        $store = $observer->getEvent()->getStore();
        if ($store->dataHasChangedFor('group_id')) {
            Mage::app()->reinitStores();
            /** @var $categoryFlatHelper Mage_Catalog_Helper_Category_Flat */
            $categoryFlatHelper = Mage::helper('Mage_Catalog_Helper_Category_Flat');
            if ($categoryFlatHelper->isAvailable() && $categoryFlatHelper->isBuilt()) {
                Mage::getResourceModel('Mage_Catalog_Model_Resource_Category_Flat')->synchronize(null, array($store->getId()));
            }
            Mage::getResourceSingleton('Mage_Catalog_Model_Resource_Product')->refreshEnabledIndex($store);
        }
        return $this;
    }

    /**
     * Process catalog data related with new store
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_Catalog_Model_Observer
     */
    public function storeAdd(Varien_Event_Observer $observer)
    {
        /* @var $store Mage_Core_Model_Store */
        $store = $observer->getEvent()->getStore();
        Mage::app()->reinitStores();
        Mage::getConfig()->reinit();
        /** @var $categoryFlatHelper Mage_Catalog_Helper_Category_Flat */
        $categoryFlatHelper = Mage::helper('Mage_Catalog_Helper_Category_Flat');
        if ($categoryFlatHelper->isAvailable() && $categoryFlatHelper->isBuilt()) {
            Mage::getResourceModel('Mage_Catalog_Model_Resource_Category_Flat')->synchronize(null, array($store->getId()));
        }
        Mage::getResourceModel('Mage_Catalog_Model_Resource_Product')->refreshEnabledIndex($store);
        return $this;
    }

    /**
     * Process catalog data related with store group root category
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_Catalog_Model_Observer
     */
    public function storeGroupSave(Varien_Event_Observer $observer)
    {
        /* @var $group Mage_Core_Model_Store_Group */
        $group = $observer->getEvent()->getGroup();
        if ($group->dataHasChangedFor('root_category_id') || $group->dataHasChangedFor('website_id')) {
            Mage::app()->reinitStores();
            foreach ($group->getStores() as $store) {
                /** @var $categoryFlatHelper Mage_Catalog_Helper_Category_Flat */
                $categoryFlatHelper = Mage::helper('Mage_Catalog_Helper_Category_Flat');
                if ($categoryFlatHelper->isAvailable() && $categoryFlatHelper->isBuilt()) {
                    Mage::getResourceModel('Mage_Catalog_Model_Resource_Category_Flat')->synchronize(null, array($store->getId()));
                }
            }
        }
        return $this;
    }

    /**
     * Process delete of store
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Catalog_Model_Observer
     */
    public function storeDelete(Varien_Event_Observer $observer)
    {
        /** @var $categoryFlatHelper Mage_Catalog_Helper_Category_Flat */
        $categoryFlatHelper = Mage::helper('Mage_Catalog_Helper_Category_Flat');
        if ($categoryFlatHelper->isAvailable() && $categoryFlatHelper->isBuilt()) {
            $store = $observer->getEvent()->getStore();
            Mage::getResourceModel('Mage_Catalog_Model_Resource_Category_Flat')->deleteStores($store->getId());
        }
        return $this;
    }

    /**
     * Process catalog data after category move
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_Catalog_Model_Observer
     */
    public function categoryMove(Varien_Event_Observer $observer)
    {
        $categoryId = $observer->getEvent()->getCategoryId();
        $prevParentId = $observer->getEvent()->getPrevParentId();
        $parentId = $observer->getEvent()->getParentId();
        /** @var $categoryFlatHelper Mage_Catalog_Helper_Category_Flat */
        $categoryFlatHelper = Mage::helper('Mage_Catalog_Helper_Category_Flat');
        if ($categoryFlatHelper->isAvailable() && $categoryFlatHelper->isBuilt()) {
            Mage::getResourceModel('Mage_Catalog_Model_Resource_Category_Flat')->move($categoryId, $prevParentId, $parentId);
        }
        return $this;
    }

    /**
     * Process catalog data after products import
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_Catalog_Model_Observer
     */
    public function catalogProductImportAfter(Varien_Event_Observer $observer)
    {
        Mage::getModel('Mage_Catalog_Model_Url')->refreshRewrites();
        Mage::getResourceSingleton('Mage_Catalog_Model_Resource_Category')->refreshProductIndex();
        return $this;
    }

    /**
     * Catalog Product Compare Items Clean
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Catalog_Model_Observer
     */
    public function catalogProductCompareClean(Varien_Event_Observer $observer)
    {
        Mage::getModel('Mage_Catalog_Model_Product_Compare_Item')->clean();
        return $this;
    }

    /**
     * After save event of category
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Catalog_Model_Observer
     */
    public function categorySaveAfter(Varien_Event_Observer $observer)
    {
        /** @var $categoryFlatHelper Mage_Catalog_Helper_Category_Flat */
        $categoryFlatHelper = Mage::helper('Mage_Catalog_Helper_Category_Flat');
        if ($categoryFlatHelper->isAvailable() && $categoryFlatHelper->isBuilt()) {
            $category = $observer->getEvent()->getCategory();
            Mage::getResourceModel('Mage_Catalog_Model_Resource_Category_Flat')->synchronize($category);
        }
        return $this;
    }

    /**
     * Checking whether the using static urls in WYSIWYG allowed event
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Catalog_Model_Observer
     */
    public function catalogCheckIsUsingStaticUrlsAllowed(Varien_Event_Observer $observer)
    {
        $storeId = $observer->getEvent()->getData('store_id');
        $result  = $observer->getEvent()->getData('result');
        $result->isAllowed = Mage::helper('Mage_Catalog_Helper_Data')->setStoreId($storeId)->isUsingStaticUrlsAllowed();
    }

    /**
     * Cron job method for product prices to reindex
     *
     * @param Mage_Cron_Model_Schedule $schedule
     */
    public function reindexProductPrices(Mage_Cron_Model_Schedule $schedule)
    {
        $indexProcess = Mage::getSingleton('Mage_Index_Model_Indexer')->getProcessByCode('catalog_product_price');
        if ($indexProcess) {
            $indexProcess->reindexAll();
        }
    }

    /**
     * Adds catalog categories to top menu
     *
     * @param Varien_Event_Observer $observer
     */
    public function addCatalogToTopmenuItems(Varien_Event_Observer $observer)
    {
        $this->_addCategoriesToMenu(Mage::helper('Mage_Catalog_Helper_Category')->getStoreCategories(), $observer->getMenu());
    }

    /**
     * Recursively adds categories to top menu
     *
     * @param Varien_Data_Tree_Node_Collection|array $categories
     * @param Varien_Data_Tree_Node $parentCategoryNode
     */
    protected function _addCategoriesToMenu($categories, $parentCategoryNode)
    {
        foreach ($categories as $category) {
            if (!$category->getIsActive()) {
                continue;
            }

            $nodeId = 'category-node-' . $category->getId();

            $tree = $parentCategoryNode->getTree();
            $categoryData = array(
                'name' => $category->getName(),
                'id' => $nodeId,
                'url' => Mage::helper('Mage_Catalog_Helper_Category')->getCategoryUrl($category),
                'is_active' => $this->_isActiveMenuCategory($category)
            );
            $categoryNode = new Varien_Data_Tree_Node($categoryData, 'id', $tree, $parentCategoryNode);
            $parentCategoryNode->addChild($categoryNode);

            if (Mage::helper('Mage_Catalog_Helper_Category_Flat')->isEnabled()) {
                $subcategories = (array)$category->getChildrenNodes();
            } else {
                $subcategories = $category->getChildren();
            }

            $this->_addCategoriesToMenu($subcategories, $categoryNode);
        }
    }

    /**
     * Checks whether category belongs to active category's path
     *
     * @param Varien_Data_Tree_Node $category
     * @return bool
     */
    protected function _isActiveMenuCategory($category)
    {
        $catalogLayer = Mage::getSingleton('Mage_Catalog_Model_Layer');
        if (!$catalogLayer) {
            return false;
        }

        $currentCategory = $catalogLayer->getCurrentCategory();
        if (!$currentCategory) {
            return false;
        }

        $categoryPathIds = explode(',', $currentCategory->getPathInStore());
        return in_array($category->getId(), $categoryPathIds);
    }
}