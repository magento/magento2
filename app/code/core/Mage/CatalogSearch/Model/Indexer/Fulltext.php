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
 * CatalogSearch fulltext indexer model
 *
 * @category    Mage
 * @package     Mage_CatalogSearch
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_CatalogSearch_Model_Indexer_Fulltext extends Mage_Index_Model_Indexer_Abstract
{
    /**
     * Data key for matching result to be saved in
     */
    const EVENT_MATCH_RESULT_KEY = 'catalogsearch_fulltext_match_result';

    /**
     * List of searchable attributes
     *
     * @var null|array
     */
    protected $_searchableAttributes = null;

    /**
     * Retrieve resource instance
     *
     * @return Mage_CatalogSearch_Model_Resource_Indexer_Fulltext
     */
    protected function _getResource()
    {
        return Mage::getResourceSingleton('Mage_CatalogSearch_Model_Resource_Indexer_Fulltext');
    }

    /**
     * Indexer must be match entities
     *
     * @var array
     */
    protected $_matchedEntities = array(
        Mage_Catalog_Model_Product::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_MASS_ACTION,
            Mage_Index_Model_Event::TYPE_DELETE
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
        Mage_Core_Model_Config_Data::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        Mage_Catalog_Model_Convert_Adapter_Product::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        Mage_Catalog_Model_Category::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        )
    );

    /**
     * Related Configuration Settings for match
     *
     * @var array
     */
    protected $_relatedConfigSettings = array(
        Mage_CatalogSearch_Model_Fulltext::XML_PATH_CATALOG_SEARCH_TYPE
    );

    /**
     * Retrieve Fulltext Search instance
     *
     * @return Mage_CatalogSearch_Model_Fulltext
     */
    protected function _getIndexer()
    {
        return Mage::getSingleton('Mage_CatalogSearch_Model_Fulltext');
    }

    /**
     * Retrieve Indexer name
     *
     * @return string
     */
    public function getName()
    {
        return Mage::helper('Mage_CatalogSearch_Helper_Data')->__('Catalog Search Index');
    }

    /**
     * Retrieve Indexer description
     *
     * @return string
     */
    public function getDescription()
    {
        return Mage::helper('Mage_CatalogSearch_Helper_Data')->__('Rebuild Catalog product fulltext search index');
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
        $data       = $event->getNewData();
        if (isset($data[self::EVENT_MATCH_RESULT_KEY])) {
            return $data[self::EVENT_MATCH_RESULT_KEY];
        }

        $entity = $event->getEntity();
        if ($entity == Mage_Catalog_Model_Resource_Eav_Attribute::ENTITY) {
            /* @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            $attribute      = $event->getDataObject();

            if (!$attribute) {
                $result = false;
            } elseif ($event->getType() == Mage_Index_Model_Event::TYPE_SAVE) {
                $result = $attribute->dataHasChangedFor('is_searchable');
            } elseif ($event->getType() == Mage_Index_Model_Event::TYPE_DELETE) {
                $result = $attribute->getIsSearchable();
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
        } else if ($entity == Mage_Core_Model_Config_Data::ENTITY) {
            $data = $event->getDataObject();
            if ($data && in_array($data->getPath(), $this->_relatedConfigSettings)) {
                $result = $data->isValueChanged();
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
                $event->addNewData('catalogsearch_fulltext_reindex_all', true);
                break;

            case Mage_Core_Model_Config_Data::ENTITY:
            case Mage_Core_Model_Store::ENTITY:
            case Mage_Catalog_Model_Resource_Eav_Attribute::ENTITY:
            case Mage_Core_Model_Store_Group::ENTITY:
                $event->addNewData('catalogsearch_fulltext_skip_call_event_handler', true);
                $process = $event->getProcess();
                $process->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
                break;
            case Mage_Catalog_Model_Category::ENTITY:
                $this->_registerCatalogCategoryEvent($event);
                break;
        }
    }

    /**
     * Get data required for category'es products reindex
     *
     * @param Mage_Index_Model_Event $event
     * @return Mage_CatalogSearch_Model_Indexer_Search
     */
    protected function _registerCatalogCategoryEvent(Mage_Index_Model_Event $event)
    {
        switch ($event->getType()) {
            case Mage_Index_Model_Event::TYPE_SAVE:
                /* @var $category Mage_Catalog_Model_Category */
                $category   = $event->getDataObject();
                $productIds = $category->getAffectedProductIds();
                if ($productIds) {
                    $event->addNewData('catalogsearch_category_update_product_ids', $productIds);
                    $event->addNewData('catalogsearch_category_update_category_ids', array($category->getId()));
                } else {
                    $movedCategoryId = $category->getMovedCategoryId();
                    if ($movedCategoryId) {
                        $event->addNewData('catalogsearch_category_update_product_ids', array());
                        $event->addNewData('catalogsearch_category_update_category_ids', array($movedCategoryId));
                    }
                }
                break;
        }

        return $this;
    }

    /**
     * Register data required by catatalog product process in event object
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

                $event->addNewData('catalogsearch_update_product_id', $product->getId());
                break;
            case Mage_Index_Model_Event::TYPE_DELETE:
                /* @var $product Mage_Catalog_Model_Product */
                $product = $event->getDataObject();

                $event->addNewData('catalogsearch_delete_product_id', $product->getId());
                break;
            case Mage_Index_Model_Event::TYPE_MASS_ACTION:
                /* @var $actionObject Varien_Object */
                $actionObject = $event->getDataObject();

                $reindexData  = array();
                $rebuildIndex = false;

                // check if status changed
                $attrData = $actionObject->getAttributesData();
                if (isset($attrData['status'])) {
                    $rebuildIndex = true;
                    $reindexData['catalogsearch_status'] = $attrData['status'];
                }

                // check changed websites
                if ($actionObject->getWebsiteIds()) {
                    $rebuildIndex = true;
                    $reindexData['catalogsearch_website_ids'] = $actionObject->getWebsiteIds();
                    $reindexData['catalogsearch_action_type'] = $actionObject->getActionType();
                }

                $searchableAttributes = array();
                if (is_array($attrData)) {
                    $searchableAttributes = array_intersect($this->_getSearchableAttributes(), array_keys($attrData));
                }

                if (count($searchableAttributes) > 0) {
                    $rebuildIndex = true;
                    $reindexData['catalogsearch_force_reindex'] = true;
                }

                // register affected products
                if ($rebuildIndex) {
                    $reindexData['catalogsearch_product_ids'] = $actionObject->getProductIds();
                    foreach ($reindexData as $k => $v) {
                        $event->addNewData($k, $v);
                    }
                }
                break;
        }

        return $this;
    }

    /**
     * Retrieve searchable attributes list
     *
     * @return array
     */
    protected function _getSearchableAttributes()
    {
        if (is_null($this->_searchableAttributes)) {
            /** @var $attributeCollection Mage_Catalog_Model_Resource_Product_Attribute_Collection */
            $attributeCollection = Mage::getResourceModel('Mage_Catalog_Model_Resource_Product_Attribute_Collection');
            $attributeCollection->addIsSearchableFilter();

            foreach ($attributeCollection as $attribute) {
                $this->_searchableAttributes[] = $attribute->getAttributeCode();
            }
        }

        return $this->_searchableAttributes;
    }

    /**
     * Check if product is composite
     *
     * @param int $productId
     * @return bool
     */
    protected function _isProductComposite($productId)
    {
        $product = Mage::getModel('Mage_Catalog_Model_Product')->load($productId);
        return $product->isComposite();
    }

    /**
     * Process event
     *
     * @param Mage_Index_Model_Event $event
     */
    protected function _processEvent(Mage_Index_Model_Event $event)
    {
        $data = $event->getNewData();

        if (!empty($data['catalogsearch_fulltext_reindex_all'])) {
            $this->reindexAll();
        } else if (!empty($data['catalogsearch_delete_product_id'])) {
            $productId = $data['catalogsearch_delete_product_id'];

            if (!$this->_isProductComposite($productId)) {
                $parentIds = $this->_getResource()->getRelationsByChild($productId);
                if (!empty($parentIds)) {
                    $this->_getIndexer()->rebuildIndex(null, $parentIds);
                }
            }

            $this->_getIndexer()->cleanIndex(null, $productId)
                ->resetSearchResults();
        } else if (!empty($data['catalogsearch_update_product_id'])) {
            $productId = $data['catalogsearch_update_product_id'];
            $productIds = array($productId);

            if (!$this->_isProductComposite($productId)) {
                $parentIds = $this->_getResource()->getRelationsByChild($productId);
                if (!empty($parentIds)) {
                    $productIds = array_merge($productIds, $parentIds);
                }
            }

            $this->_getIndexer()->rebuildIndex(null, $productIds)
                ->resetSearchResults();
        } else if (!empty($data['catalogsearch_product_ids'])) {
            // mass action
            $productIds = $data['catalogsearch_product_ids'];

            if (!empty($data['catalogsearch_website_ids'])) {
                $websiteIds = $data['catalogsearch_website_ids'];
                $actionType = $data['catalogsearch_action_type'];

                foreach ($websiteIds as $websiteId) {
                    foreach (Mage::app()->getWebsite($websiteId)->getStoreIds() as $storeId) {
                        if ($actionType == 'remove') {
                            $this->_getIndexer()
                                ->cleanIndex($storeId, $productIds)
                                ->resetSearchResults();
                        } else if ($actionType == 'add') {
                            $this->_getIndexer()
                                ->rebuildIndex($storeId, $productIds)
                                ->resetSearchResults();
                        }
                    }
                }
            }
            if (isset($data['catalogsearch_status'])) {
                $status = $data['catalogsearch_status'];
                if ($status == Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
                    $this->_getIndexer()
                        ->rebuildIndex(null, $productIds)
                        ->resetSearchResults();
                } else {
                    $this->_getIndexer()
                        ->cleanIndex(null, $productIds)
                        ->resetSearchResults();
                }
            }
            if (isset($data['catalogsearch_force_reindex'])) {
                $this->_getIndexer()
                    ->rebuildIndex(null, $productIds)
                    ->resetSearchResults();
            }
        } else if (isset($data['catalogsearch_category_update_product_ids'])) {
            $productIds = $data['catalogsearch_category_update_product_ids'];
            $categoryIds = $data['catalogsearch_category_update_category_ids'];

            $this->_getIndexer()
                ->updateCategoryIndex($productIds, $categoryIds);
        }
    }

    /**
     * Rebuild all index data
     *
     */
    public function reindexAll()
    {
        $resourceModel = $this->_getIndexer()->getResource();
        $resourceModel->beginTransaction();
        try {
            $this->_getIndexer()->rebuildIndex();
            $resourceModel->commit();
        } catch (Exception $e) {
            $resourceModel->rollBack();
            throw $e;
        }
    }
}
