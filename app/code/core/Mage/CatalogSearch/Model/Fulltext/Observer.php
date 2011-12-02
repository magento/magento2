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
 * @package     Mage_CatalogSearch
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * CatalogSearch Fulltext Observer
 *
 * @category    Mage
 * @package     Mage_CatalogSearch
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_CatalogSearch_Model_Fulltext_Observer
{
    /**
     * Retrieve fulltext (indexer) model
     *
     * @return Mage_CatalogSearch_Model_Fulltext
     */
    protected function _getFulltextModel()
    {
        return Mage::getSingleton('Mage_CatalogSearch_Model_Fulltext');
    }

    /**
     * Update product index when product data updated
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_CatalogSearch_Model_Fulltext_Observer
     */
    public function refreshProductIndex(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();

        $this->_getFulltextModel()
            ->rebuildIndex(null, $product->getId())
            ->resetSearchResults();

        return $this;
    }

    /**
     * Clean product index when product deleted or marked as unsearchable/invisible
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_CatalogSearch_Model_Fulltext_Observer
     */
    public function cleanProductIndex(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();

        $this->_getFulltextModel()
            ->cleanIndex(null, $product->getId())
            ->resetSearchResults();

        return $this;
    }

    /**
     * Update all attribute-dependant index
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_CatalogSearch_Model_Fulltext_Observer
     */
    public function eavAttributeChange(Varien_Event_Observer $observer)
    {
        $attribute = $observer->getEvent()->getAttribute();
        /* @var $attribute Mage_Eav_Model_Entity_Attribute */
        $entityType = Mage::getSingleton('Mage_Eav_Model_Config')->getEntityType(Mage_Catalog_Model_Product::ENTITY);
        /* @var $entityType Mage_Eav_Model_Entity_Type */

        if ($attribute->getEntityTypeId() != $entityType->getId()) {
            return $this;
        }
        $delete = $observer->getEventName() == 'eav_entity_attribute_delete_after';

        if (!$delete && !$attribute->dataHasChangedFor('is_searchable')) {
            return $this;
        }

        $showNotice = false;
        if ($delete) {
            if ($attribute->getIsSearchable()) {
                $showNotice = true;
            }
        }
        elseif ($attribute->dataHasChangedFor('is_searchable')) {
            $showNotice = true;
        }

        if ($showNotice) {
            $url = Mage::getSingleton('Mage_Adminhtml_Model_Url')->getUrl('adminhtml/system_cache');
            Mage::getSingleton('Mage_Adminhtml_Model_Session')->addNotice(
                Mage::helper('Mage_CatalogSearch_Helper_Data')->__('Attribute setting change related with Search Index. Please run <a href="%s">Rebuild Search Index</a> process.', $url)
            );
        }

        return $this;
    }

    /**
     * Rebuild index after import
     *
     * @return Mage_CatalogSearch_Model_Fulltext_Observer
     */
    public function refreshIndexAfterImport()
    {
        $this->_getFulltextModel()
            ->rebuildIndex();
        return $this;
    }

    /**
     * Refresh fulltext index when we add new store
     *
     * @param   Varien_Event_Observer $observer
     * @return  Mage_CatalogSearch_Model_Fulltext_Observer
     */
    public function refreshStoreIndex(Varien_Event_Observer $observer)
    {
        $storeId = $observer->getEvent()->getStore()->getId();
        $this->_getFulltextModel()->rebuildIndex($storeId);
        return $this;
    }

    /**
     * Catalog Product mass website update
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_CatalogSearch_Model_Fulltext_Observer
     */
    public function catalogProductWebsiteUpdate(Varien_Event_Observer $observer)
    {
        $websiteIds = $observer->getEvent()->getWebsiteIds();
        $productIds = $observer->getEvent()->getProductIds();
        $actionType = $observer->getEvent()->getAction();

        foreach ($websiteIds as $websiteId) {
            foreach (Mage::app()->getWebsite($websiteId)->getStoreIds() as $storeId) {
                if ($actionType == 'remove') {
                    $this->_getFulltextModel()
                        ->cleanIndex($storeId, $productIds)
                        ->resetSearchResults();
                }
                elseif ($actionType == 'add') {
                    $this->_getFulltextModel()
                        ->rebuildIndex($storeId, $productIds)
                        ->resetSearchResults();
                }
            }
        }

        return $this;
    }

    /**
     * Store delete processing
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_CatalogSearch_Model_Fulltext_Observer
     */
    public function cleanStoreIndex(Varien_Event_Observer $observer)
    {
        $store = $observer->getEvent()->getStore();
        /* @var $store Mage_Core_Model_Store */

        $this->_getFulltextModel()
            ->cleanIndex($store->getId());

        return $this;
    }
}
