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
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Catalog Category Flat Indexer Model
 *
 * @category    Mage
 * @package     Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Category_Indexer_Flat extends Mage_Index_Model_Indexer_Abstract
{
    /**
     * Data key for matching result to be saved in
     */
    const EVENT_MATCH_RESULT_KEY = 'catalog_category_flat_match_result';

    /**
     * Matched entity events
     *
     * @var array
     */
    protected $_matchedEntities = array(
        Mage_Catalog_Model_Category::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
        Mage_Core_Model_Store::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE,
            Mage_Index_Model_Event::TYPE_DELETE
        ),
        Mage_Core_Model_Store_Group::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        ),
    );

    /**
     * Retrieve Indexer name
     *
     * @return string
     */
    public function getName()
    {
        return Mage::helper('Mage_Catalog_Helper_Data')->__('Category Flat Data');
    }

    /**
     * Retrieve Indexer description
     *
     * @return string
     */
    public function getDescription()
    {
        return Mage::helper('Mage_Catalog_Helper_Data')->__('Reorganize EAV category structure to flat structure');
    }

    /**
     * Retrieve Catalog Category Flat Indexer model
     *
     * @return Mage_Catalog_Model_Resource_Category_Flat
     */
    protected function _getIndexer()
    {
        return Mage::getResourceSingleton('Mage_Catalog_Model_Resource_Category_Flat');
    }

    /**
     * Check if event can be matched by process
     * Overwrote for check is flat catalog category is enabled and specific save
     * category, store, store_group
     *
     * @param Mage_Index_Model_Event $event
     * @return bool
     */
    public function matchEvent(Mage_Index_Model_Event $event)
    {
        if (!Mage::helper('Mage_Catalog_Helper_Category_Flat')->isEnabled(true)) {
            return false;
        }

        $data = $event->getNewData();
        if (isset($data[self::EVENT_MATCH_RESULT_KEY])) {
            return $data[self::EVENT_MATCH_RESULT_KEY];
        }

        $entity = $event->getEntity();
        if ($entity == Mage_Core_Model_Store::ENTITY) {
            if ($event->getType() == Mage_Index_Model_Event::TYPE_DELETE) {
                $result = true;
            } elseif ($event->getType() == Mage_Index_Model_Event::TYPE_SAVE) {
                /** @var $store Mage_Core_Model_Store */
                $store = $event->getDataObject();
                if ($store && ($store->isObjectNew()
                    || $store->dataHasChangedFor('group_id')
                    || $store->dataHasChangedFor('root_category_id')
                )) {
                    $result = true;
                } else {
                    $result = false;
                }
            } else {
                $result = false;
            }
        } elseif ($entity == Mage_Core_Model_Store_Group::ENTITY) {
            /** @var $storeGroup Mage_Core_Model_Store_Group */
            $storeGroup = $event->getDataObject();
            if ($storeGroup
                && ($storeGroup->dataHasChangedFor('website_id') || $storeGroup->dataHasChangedFor('root_category_id'))
            ) {
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
            case Mage_Catalog_Model_Category::ENTITY:
                $this->_registerCatalogCategoryEvent($event);
                break;

            case Mage_Core_Model_Store::ENTITY:
                if ($event->getType() == Mage_Index_Model_Event::TYPE_DELETE) {
                    $this->_registerCoreStoreEvent($event);
                    break;
                }
            case Mage_Core_Model_Store_Group::ENTITY:
                $event->addNewData('catalog_category_flat_skip_call_event_handler', true);
                $process = $event->getProcess();
                $process->changeStatus(Mage_Index_Model_Process::STATUS_REQUIRE_REINDEX);
                break;
        }
    }

    /**
     * Register data required by catalog category process in event object
     *
     * @param Mage_Index_Model_Event $event
     * @return Mage_Catalog_Model_Category_Indexer_Flat
     */
    protected function _registerCatalogCategoryEvent(Mage_Index_Model_Event $event)
    {
        switch ($event->getType()) {
            case Mage_Index_Model_Event::TYPE_SAVE:
                /* @var $category Mage_Catalog_Model_Category */
                $category = $event->getDataObject();

                /**
                 * Check if category has another affected category ids (category move result)
                 */
                $affectedCategoryIds = $category->getAffectedCategoryIds();
                if ($affectedCategoryIds) {
                    $event->addNewData('catalog_category_flat_affected_category_ids', $affectedCategoryIds);
                } else {
                    $event->addNewData('catalog_category_flat_category_id', $category->getId());
                }

                break;
        }
        return $this;
    }

    /**
     * Register core store delete process
     *
     * @param Mage_Index_Model_Event $event
     * @return Mage_Catalog_Model_Category_Indexer_Flat
     */
    protected function _registerCoreStoreEvent(Mage_Index_Model_Event $event)
    {
        if ($event->getType() == Mage_Index_Model_Event::TYPE_DELETE) {
            /* @var $store Mage_Core_Model_Store */
            $store = $event->getDataObject();
            $event->addNewData('catalog_category_flat_delete_store_id', $store->getId());
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

        if (!empty($data['catalog_category_flat_reindex_all'])) {
            $this->reindexAll();
        } else if (!empty($data['catalog_category_flat_category_id'])) {
            // catalog_product save
            $categoryId = $data['catalog_category_flat_category_id'];
            $this->_getIndexer()->synchronize($categoryId);
        } else if (!empty($data['catalog_category_flat_affected_category_ids'])) {
            $categoryIds = $data['catalog_category_flat_affected_category_ids'];
            $this->_getIndexer()->move($categoryIds);
        } else if (!empty($data['catalog_category_flat_delete_store_id'])) {
            $storeId = $data['catalog_category_flat_delete_store_id'];
            $this->_getIndexer()->deleteStores($storeId);
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
