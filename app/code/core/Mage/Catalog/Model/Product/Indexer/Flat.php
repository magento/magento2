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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Catalog_Model_Product_Indexer_Flat extends Mage_Index_Model_Indexer_Abstract
{
    /**
     * Data key for matching result to be saved in
     */
    const EVENT_MATCH_RESULT_KEY = 'catalog_product_flat_match_result';

    /**
     * Index math Entities array
     *
     * @var array
     */
    protected $_matchedEntities = array(
        Mage_Catalog_Model_Product::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_MASS_ACTION,
        ),
        Mage_Catalog_Model_Resource_Eav_Attribute::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_DELETE,
        ),
        Mage_Core_Model_Store::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_DELETE
        ),
        Mage_Core_Model_Store_Group::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        Mage_Catalog_Model_Convert_Adapter_Product::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        Mage_Catalog_Model_Product_Flat_Indexer::ENTITY => array(
            Mage_Catalog_Model_Product_Flat_Indexer::EVENT_TYPE_REBUILD,
        ),
    );

    /**
     * Retrieve Indexer name
     *
     * @return string
     */
    public function getName()
    {
        return Mage::helper('Mage_Catalog_Helper_Data')->__('Product Flat Data');
    }

    /**
     * Retrieve Indexer description
     *
     * @return string
     */
    public function getDescription()
    {
        return Mage::helper('Mage_Catalog_Helper_Data')->__('Reorganize EAV product structure to flat structure');
    }

    /**
     * Retrieve Catalog Product Flat Indexer model
     *
     * @return Mage_Catalog_Model_Product_Flat_Indexer
     */
    protected function _getIndexer()
    {
        return Mage::getSingleton('Mage_Catalog_Model_Product_Flat_Indexer');
    }

    /**
     * Check if event can be matched by process
     * Overwrote for check is flat catalog product is enabled and specific save
     * attribute, store, store_group
     *
     * @param Mage_Index_Model_Event $event
     * @return bool
     */
    public function matchEvent(Mage_Index_Model_Event $event)
    {
        if (!Mage::helper('Mage_Catalog_Helper_Product_Flat')->isBuilt()) {
            return false;
        }

        $data       = $event->getNewData();
        if (isset($data[self::EVENT_MATCH_RESULT_KEY])) {
            return $data[self::EVENT_MATCH_RESULT_KEY];
        }

        $entity = $event->getEntity();
        if ($entity == Mage_Catalog_Model_Resource_Eav_Attribute::ENTITY) {
            /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            $attribute      = $event->getDataObject();
            $addFilterable  = Mage::helper('Mage_Catalog_Helper_Product_Flat')->isAddFilterableAttributes();

            $enableBefore   = $attribute && (($attribute->getOrigData('backend_type') == 'static')
                || ($addFilterable && $attribute->getOrigData('is_filterable') > 0)
                || ($attribute->getOrigData('used_in_product_listing') == 1)
                || ($attribute->getOrigData('is_used_for_promo_rules') == 1)
                || ($attribute->getOrigData('used_for_sort_by') == 1));

            $enableAfter    = $attribute && (($attribute->getData('backend_type') == 'static')
                || ($addFilterable && $attribute->getData('is_filterable') > 0)
                || ($attribute->getData('used_in_product_listing') == 1)
                || ($attribute->getData('is_used_for_promo_rules') == 1)
                || ($attribute->getData('used_for_sort_by') == 1));

            if ($attribute && $event->getType() == Mage_Index_Model_Event::TYPE_DELETE) {
                $result = $enableBefore;
            } elseif ($attribute && $event->getType() == Mage_Index_Model_Event::TYPE_SAVE) {
                if ($enableAfter || $enableBefore) {
                    $result = true;
                } else {
                    $result = false;
                }
            } else {
                $result = false;
            }
        } else if ($entity == Mage_Core_Model_Store::ENTITY) {
            if ($event->getType() == Mage_Index_Model_Event::TYPE_DELETE) {
                $result = true;
            } else {
                /* @var $store Mage_Core_Model_Store */
                $store = $event->getDataObject();
                if ($store && $store->isObjectNew()) {
                    $result = true;
                } else {
                    $result = false;
                }
            }
        } else if ($entity == Mage_Core_Model_Store_Group::ENTITY) {
            /* @var $storeGroup Mage_Core_Model_Store_Group */
            $storeGroup = $event->getDataObject();
            if ($storeGroup && $storeGroup->dataHasChangedFor('website_id')) {
                $result = true;
            } else {
                $result = false;
            }
        } else {
            $result = parent::matchEvent($event);
        }

        $event->addNewData(self::EVENT_MATCH_RESULT_KEY, $result);

        return $result;
    }

    /**
     * Register data required by process in event object
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
        $event->addNewData(self::EVENT_MATCH_RESULT_KEY, true);
        switch ($event->getEntity()) {
            case Mage_Catalog_Model_Product::ENTITY:
                $this->_registerCatalogProductEvent($event);
                break;
            case Mage_Catalog_Model_Convert_Adapter_Product::ENTITY:
                $event->addNewData('catalog_product_flat_reindex_all', true);
                break;
            case Mage_Core_Model_Store::ENTITY:
                if ($event->getType() == Mage_Index_Model_Event::TYPE_DELETE) {
                    $this->_registerCoreStoreEvent($event);
                    break;
                }
            case Mage_Catalog_Model_Resource_Eav_Attribute::ENTITY:
            case Mage_Core_Model_Store_Group::ENTITY:
                $event->addNewData('catalog_product_flat_skip_call_event_handler', true);
                $process = $event->getProcess();
                $process->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
                break;
            case Mage_Catalog_Model_Product_Flat_Indexer::ENTITY:
                switch ($event->getType()) {
                    case Mage_Catalog_Model_Product_Flat_Indexer::EVENT_TYPE_REBUILD:
                        $event->addNewData('id', $event->getDataObject()->getId());
                }
                break;
        }
    }

    /**
     * Register data required by catalog product process in event object
     *
     * @param Mage_Index_Model_Event $event
     * @return Mage_CatalogSearch_Model_Indexer_Search
     */
    protected function _registerCatalogProductEvent(Mage_Index_Model_Event $event)
    {
        switch ($event->getType()) {
            case Mage_Index_Model_Event::TYPE_SAVE:
                /* @var $product Mage_Catalog_Model_Product */
                $product = $event->getDataObject();
                $event->addNewData('catalog_product_flat_product_id', $product->getId());
                break;

            case Mage_Index_Model_Event::TYPE_MASS_ACTION:
                /* @var $actionObject Varien_Object */
                $actionObject = $event->getDataObject();

                $reindexData  = array();
                $reindexFlat  = false;

                // check if status changed
                $attrData = $actionObject->getAttributesData();
                if (isset($attrData['status'])) {
                    $reindexFlat = true;
                    $reindexData['catalog_product_flat_status'] = $attrData['status'];
                }

                // check changed websites
                if ($actionObject->getWebsiteIds()) {
                    $reindexFlat = true;
                    $reindexData['catalog_product_flat_website_ids'] = $actionObject->getWebsiteIds();
                    $reindexData['catalog_product_flat_action_type'] = $actionObject->getActionType();
                }

                if (isset($attrData['price']) || isset($attrData['name'])) {
                    $reindexFlat = true;
                    $reindexData['catalog_product_flat_force_update'] = true;
                }

                // register affected products
                if ($reindexFlat) {
                    $reindexData['catalog_product_flat_product_ids'] = $actionObject->getProductIds();
                    foreach ($reindexData as $k => $v) {
                        $event->addNewData($k, $v);
                    }
                }
                break;
        }

        return $this;
    }

    /**
     * Register core store delete process
     *
     * @param Mage_Index_Model_Event $event
     * @return Mage_Catalog_Model_Product_Indexer_Flat
     */
    protected function _registerCoreStoreEvent(Mage_Index_Model_Event $event)
    {
        if ($event->getType() == Mage_Index_Model_Event::TYPE_DELETE) {
            /* @var $store Mage_Core_Model_Store */
            $store = $event->getDataObject();
            $event->addNewData('catalog_product_flat_delete_store_id', $store->getId());
        }
        return $this;
    }

    /**
     * Process event
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _processEvent(Mage_Index_Model_Event $event)
    {
        $data = $event->getNewData();
        if ($event->getType() == Mage_Catalog_Model_Product_Flat_Indexer::EVENT_TYPE_REBUILD) {
            $this->_getIndexer()->getResource()->rebuild($data['id']);
            return;
        }


        if (!empty($data['catalog_product_flat_reindex_all'])) {
            $this->reindexAll();
        } else if (!empty($data['catalog_product_flat_product_id'])) {
            // catalog_product save
            $productId = $data['catalog_product_flat_product_id'];
            $this->_getIndexer()->saveProduct($productId);
        } else if (!empty($data['catalog_product_flat_product_ids'])) {
            // catalog_product mass_action
            $productIds = $data['catalog_product_flat_product_ids'];

            if (!empty($data['catalog_product_flat_website_ids'])) {
                $websiteIds = $data['catalog_product_flat_website_ids'];
                foreach ($websiteIds as $websiteId) {
                    $website = Mage::app()->getWebsite($websiteId);
                    foreach ($website->getStores() as $store) {
                        if ($data['catalog_product_flat_action_type'] == 'remove') {
                            $this->_getIndexer()->removeProduct($productIds, $store->getId());
                        } else {
                            $this->_getIndexer()->updateProduct($productIds, $store->getId());
                        }
                    }
                }
            }

            if (isset($data['catalog_product_flat_status'])) {
                $status = $data['catalog_product_flat_status'];
                $this->_getIndexer()->updateProductStatus($productIds, $status);
            }

            if (isset($data['catalog_product_flat_force_update'])) {
                $this->_getIndexer()->updateProduct($productIds);
            }
        } else if (!empty($data['catalog_product_flat_delete_store_id'])) {
            $this->_getIndexer()->deleteStore($data['catalog_product_flat_delete_store_id']);
        }
    }

    /**
     * Rebuild all index data
     *
     */
    public function reindexAll()
    {
        $this->_getIndexer()->reindexAll();
    }
}
