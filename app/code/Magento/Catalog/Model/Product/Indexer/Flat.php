<?php

namespace Magento\Catalog\Model\Product\Indexer;

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
 * @category    Magento
 * @package     Magento_Catalog
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Flat extends \Magento\Index\Model\Indexer\AbstractIndexer
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
        \Magento\Catalog\Model\Product::ENTITY => array(
            \Magento\Index\Model\Event::TYPE_SAVE,
            \Magento\Index\Model\Event::TYPE_MASS_ACTION,
        ),
        \Magento\Catalog\Model\Resource\Eav\Attribute::ENTITY => array(
            \Magento\Index\Model\Event::TYPE_SAVE,
            \Magento\Index\Model\Event::TYPE_DELETE,
        ),
        \Magento\Core\Model\Store::ENTITY => array(
            \Magento\Index\Model\Event::TYPE_SAVE,
            \Magento\Index\Model\Event::TYPE_DELETE
        ),
        \Magento\Core\Model\Store\Group::ENTITY => array(
            \Magento\Index\Model\Event::TYPE_SAVE
        ),
        \Magento\Catalog\Model\Product\Flat\Indexer::ENTITY => array(
            \Magento\Catalog\Model\Product\Flat\Indexer::EVENT_TYPE_REBUILD,
        ),
    );

    /**
     * Whether the indexer should be displayed on process/list page
     *
     * @return bool
     */
    /**
     * Catalog product flat
     *
     * @var \Magento\Catalog\Helper\Product\Flat
     */
    protected $_catalogProductFlat = null;

    /**
     * Catalog product flat indexer
     *
     * @var \Magento\Catalog\Model\Product\Flat\Indexer
     */
    protected $_catalogProductFlatIndexer;

    /**
     * Store manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Flat indexer factory
     *
     * @var \Magento\Catalog\Model\Product\Flat\IndexerFactory
     */
    protected $_flatIndexerFactory;

    /**
     * Construct
     *
     * @param \Magento\Catalog\Model\Product\Flat\IndexerFactory $flatIndexerFactory
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product\Flat\Indexer $catalogProductFlatIndexer
     * @param \Magento\Catalog\Helper\Product\Flat $catalogProductFlat
     * @param \Magento\Core\Model\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param \Magento\Core\Model\Resource\AbstractResource $resource
     * @param \Magento\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Flat\IndexerFactory $flatIndexerFactory,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Flat\Indexer $catalogProductFlatIndexer,
        \Magento\Catalog\Helper\Product\Flat $catalogProductFlat,
        \Magento\Core\Model\Context $context,
        \Magento\Core\Model\Registry $registry,
        \Magento\Core\Model\Resource\AbstractResource $resource = null,
        \Magento\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_flatIndexerFactory = $flatIndexerFactory;
        $this->_storeManager = $storeManager;
        $this->_catalogProductFlatIndexer = $catalogProductFlatIndexer;
        $this->_catalogProductFlat = $catalogProductFlat;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    public function isVisible()
    {
        /** @var $productFlatHelper \Magento\Catalog\Helper\Product\Flat */
        $productFlatHelper = $this->_catalogProductFlat;
        return $productFlatHelper->isEnabled() || !$productFlatHelper->isBuilt();
    }

    /**
     * Retrieve Indexer name
     *
     * @return string
     */
    public function getName()
    {
        return __('Product Flat Data');
    }

    /**
     * Retrieve Indexer description
     *
     * @return string
     */
    public function getDescription()
    {
        return __('Reorganize EAV product structure to flat structure');
    }

    /**
     * Check if event can be matched by process
     * Overwrote for check is flat catalog product is enabled and specific save
     * attribute, store, store_group
     *
     * @param \Magento\Index\Model\Event $event
     * @return bool
     */
    public function matchEvent(\Magento\Index\Model\Event $event)
    {
        /** @var $productFlatHelper \Magento\Catalog\Helper\Product\Flat */
        $productFlatHelper = $event->getFlatHelper() ?: $this->_catalogProductFlat;
        if (!$productFlatHelper->isAvailable() || !$productFlatHelper->isBuilt()) {
            return false;
        }

        $data = $event->getNewData();
        if (isset($data[self::EVENT_MATCH_RESULT_KEY])) {
            return $data[self::EVENT_MATCH_RESULT_KEY];
        }

        $entity = $event->getEntity();
        switch ($entity) {
            case \Magento\Catalog\Model\Resource\Eav\Attribute::ENTITY:
                $result = $this->_matchAttributeEvent($event, $productFlatHelper);
                break;

            case \Magento\Core\Model\Store::ENTITY:
                $result = $this->_matchStoreEvent($event);
                break;

            case \Magento\Core\Model\Store\Group::ENTITY:
                $result = $this->_matchStoreGroupEvent($event);
                break;

            default:
                $result = parent::matchEvent($event);
                break;
        }

        $event->addNewData(self::EVENT_MATCH_RESULT_KEY, $result);

        return $result;
    }

    /**
     * Whether a store group available for matching or not
     *
     * @param \Magento\Index\Model\Event $event
     * @return bool
     */
    protected function _matchStoreGroupEvent(\Magento\Index\Model\Event $event)
     {
         /* @var $storeGroup \Magento\Core\Model\Store\Group */
         $storeGroup = $event->getDataObject();
         if ($storeGroup && $storeGroup->dataHasChangedFor('website_id')) {
             return true;
         }
         return false;
     }

    /**
     * Whether a store available for matching or not
     *
     * @param \Magento\Index\Model\Event $event
     * @return bool
     */
    protected function _matchStoreEvent(\Magento\Index\Model\Event $event)
    {
        if ($event->getType() == \Magento\Index\Model\Event::TYPE_DELETE) {
            return true;
        } else {
            /* @var $store \Magento\Core\Model\Store */
            $store = $event->getDataObject();
            if ($store && $store->isObjectNew()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether an attribute available for matching or not
     *
     * @param \Magento\Index\Model\Event $event
     * @param $productFlatHelper
     * @return bool
     */
    protected function _matchAttributeEvent(\Magento\Index\Model\Event $event, $productFlatHelper)
    {
        $attribute = $event->getDataObject();
        if (!$attribute) {
            return false;
        }

        $enableBefore = $this->_isAttributeEnabled($attribute, $productFlatHelper);
        $enableAfter = $this->_isAttributeEnabled($attribute, $productFlatHelper, false);

        if ($event->getType() == \Magento\Index\Model\Event::TYPE_DELETE) {
            return $enableBefore;
        } elseif ($event->getType() == \Magento\Index\Model\Event::TYPE_SAVE && ($enableAfter || $enableBefore)) {
            return true;
        }

        return false;
    }

    /**
     * Whether an attribute available for matching or not
     *
     * @param \Magento\Catalog\Model\Resource\Eav\Attribute $attribute
     * @param \Magento\Catalog\Helper\Product\Flat $productFlatHelper
     * @param bool $before
     * @return bool
     */
    protected function _isAttributeEnabled($attribute, $productFlatHelper, $before = true) {

        $method = $before ? 'getOrigData': 'getData';

        return $attribute && (($attribute->$method('backend_type') == 'static')
            || ($productFlatHelper->isAddFilterableAttributes() && $attribute->$method('is_filterable') > 0)
            || ($attribute->$method('used_in_product_listing') == 1)
            || ($attribute->$method('is_used_for_promo_rules') == 1)
            || ($attribute->$method('used_for_sort_by') == 1));
    }

    /**
     * Register data required by process in event object
     *
     * @param \Magento\Index\Model\Event $event
     */
    protected function _registerEvent(\Magento\Index\Model\Event $event)
    {
        $event->addNewData(self::EVENT_MATCH_RESULT_KEY, true);
        switch ($event->getEntity()) {
            case \Magento\Catalog\Model\Product::ENTITY:
                $this->_registerCatalogProductEvent($event);
                break;
            case \Magento\Core\Model\Store::ENTITY:
                if ($event->getType() == \Magento\Index\Model\Event::TYPE_DELETE) {
                    $this->_registerCoreStoreEvent($event);
                    break;
                }
            case \Magento\Catalog\Model\Resource\Eav\Attribute::ENTITY:
            case \Magento\Core\Model\Store\Group::ENTITY:
                $event->addNewData('catalog_product_flat_skip_call_event_handler', true);
                $process = $event->getProcess();
                $process->changeStatus(\Magento\Index\Model\Process::STATUS_REQUIRE_REINDEX);
                break;
            case \Magento\Catalog\Model\Product\Flat\Indexer::ENTITY:
                switch ($event->getType()) {
                    case \Magento\Catalog\Model\Product\Flat\Indexer::EVENT_TYPE_REBUILD:
                        $event->addNewData('id', $event->getDataObject()->getId());
                }
                break;
        }
    }

    /**
     * Register data required by catalog product process in event object
     *
     * @param \Magento\Index\Model\Event $event
     * @return \Magento\Catalog\Model\Product\Indexer\Flat
     */
    protected function _registerCatalogProductEvent(\Magento\Index\Model\Event $event)
    {
        switch ($event->getType()) {
            case \Magento\Index\Model\Event::TYPE_SAVE:
                /* @var $product \Magento\Catalog\Model\Product */
                $product = $event->getDataObject();
                $event->addNewData('catalog_product_flat_product_id', $product->getId());
                break;

            case \Magento\Index\Model\Event::TYPE_MASS_ACTION:
                /* @var $actionObject \Magento\Object */
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

                $flatAttributes = array();
                if (is_array($attrData)) {
                    $flatAttributes = array_intersect($this->_getFlatAttributes(), array_keys($attrData));
                }

                if (count($flatAttributes) > 0) {
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
     * @param \Magento\Index\Model\Event $event
     * @return \Magento\Catalog\Model\Product\Indexer\Flat
     */
    protected function _registerCoreStoreEvent(\Magento\Index\Model\Event $event)
    {
        if ($event->getType() == \Magento\Index\Model\Event::TYPE_DELETE) {
            /* @var $store \Magento\Core\Model\Store */
            $store = $event->getDataObject();
            $event->addNewData('catalog_product_flat_delete_store_id', $store->getId());
        }
        return $this;
    }

    /**
     * Process event
     *
     * @param \Magento\Index\Model\Event $event
     */
    protected function _processEvent(\Magento\Index\Model\Event $event)
    {
        $data = $event->getNewData();
        if ($event->getType() == \Magento\Catalog\Model\Product\Flat\Indexer::EVENT_TYPE_REBUILD) {
            $this->_catalogProductFlatIndexer->getResource()->rebuild($data['id']);
            return;
        }


        if (!empty($data['catalog_product_flat_reindex_all'])) {
            $this->reindexAll();
        } else if (!empty($data['catalog_product_flat_product_id'])) {
            // catalog_product save
            $productId = $data['catalog_product_flat_product_id'];
            $this->_catalogProductFlatIndexer->saveProduct($productId);
        } else if (!empty($data['catalog_product_flat_product_ids'])) {
            // catalog_product mass_action
            $productIds = $data['catalog_product_flat_product_ids'];

            if (!empty($data['catalog_product_flat_website_ids'])) {
                $websiteIds = $data['catalog_product_flat_website_ids'];
                foreach ($websiteIds as $websiteId) {
                    $website = $this->_storeManager->getWebsite($websiteId);
                    foreach ($website->getStores() as $store) {
                        if ($data['catalog_product_flat_action_type'] == 'remove') {
                            $this->_catalogProductFlatIndexer->removeProduct($productIds, $store->getId());
                        } else {
                            $this->_catalogProductFlatIndexer->updateProduct($productIds, $store->getId());
                        }
                    }
                }
            }

            if (isset($data['catalog_product_flat_status'])) {
                $status = $data['catalog_product_flat_status'];
                $this->_catalogProductFlatIndexer->updateProductStatus($productIds, $status);
            }

            if (isset($data['catalog_product_flat_force_update'])) {
                $this->_catalogProductFlatIndexer->updateProduct($productIds);
            }
        } else if (!empty($data['catalog_product_flat_delete_store_id'])) {
            $this->_catalogProductFlatIndexer->deleteStore($data['catalog_product_flat_delete_store_id']);
        }
    }

    /**
     * Rebuild all index data
     *
     */
    public function reindexAll()
    {
        $this->_catalogProductFlatIndexer->reindexAll();
    }

    /**
     * Retrieve list of attribute codes, that are used in flat
     *
     * @return array
     */
    protected function _getFlatAttributes()
    {
        return $this->_flatIndexerFactory->create()->getAttributeCodes();
    }
}
