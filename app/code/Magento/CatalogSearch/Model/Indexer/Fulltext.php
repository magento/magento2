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
 * @category    Magento
 * @package     Magento_CatalogSearch
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * CatalogSearch fulltext indexer model
 */
namespace Magento\CatalogSearch\Model\Indexer;

use Magento\App\Config\ValueInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Resource\Eav\Attribute;
use Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory;
use Magento\CatalogSearch\Model\Fulltext as ModelFulltext;
use Magento\CatalogSearch\Model\Resource\Indexer\Fulltext as IndexerFulltext;
use Magento\Model\Context;
use Magento\Registry;
use Magento\Model\Resource\AbstractResource;
use Magento\Core\Model\Store;
use Magento\Core\Model\Store\Group;
use Magento\Core\Model\StoreManagerInterface;
use Magento\Data\Collection\Db;
use Magento\Index\Model\Event;
use Magento\Index\Model\Indexer\AbstractIndexer;
use Magento\Index\Model\Process;

class Fulltext extends AbstractIndexer
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
    protected $_searchableAttributes;

    /**
     * Store manager
     *
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Product factory
     *
     * @var ProductFactory
     */
    protected $_productFactory;

    /**
     * Catalog search fulltext
     *
     * @var ModelFulltext
     */
    protected $_catalogSearchFulltext;

    /**
     * Attribute collection factory
     *
     * @var CollectionFactory
     */
    protected $_attributeCollectionFactory;

    /**
     * Catalog search indexer fulltext
     *
     * @var IndexerFulltext
     */
    protected $_catalogSearchIndexerFulltext;

    /**
     * Construct
     *
     * @param Context $context
     * @param Registry $registry
     * @param IndexerFulltext $catalogSearchIndexerFulltext
     * @param CollectionFactory $attributeCollectionFactory
     * @param ModelFulltext $catalogSearchFulltext
     * @param ProductFactory $productFactory
     * @param StoreManagerInterface $storeManager
     * @param AbstractResource $resource
     * @param Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        IndexerFulltext $catalogSearchIndexerFulltext,
        CollectionFactory $attributeCollectionFactory,
        ModelFulltext $catalogSearchFulltext,
        ProductFactory $productFactory,
        StoreManagerInterface $storeManager,
        AbstractResource $resource = null,
        Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_catalogSearchIndexerFulltext = $catalogSearchIndexerFulltext;
        $this->_attributeCollectionFactory = $attributeCollectionFactory;
        $this->_catalogSearchFulltext = $catalogSearchFulltext;
        $this->_productFactory = $productFactory;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve resource instance
     *
     * @return IndexerFulltext
     */
    protected function _getResource()
    {
        return $this->_catalogSearchIndexerFulltext;
    }

    /**
     * Indexer must be match entities
     *
     * @var array
     */
    protected $_matchedEntities = array(
        Product::ENTITY => array(Event::TYPE_SAVE, Event::TYPE_MASS_ACTION, Event::TYPE_DELETE),
        Attribute::ENTITY => array(Event::TYPE_SAVE, Event::TYPE_DELETE),
        Store::ENTITY => array(Event::TYPE_SAVE, Event::TYPE_DELETE),
        Group::ENTITY => array(Event::TYPE_SAVE),
        ValueInterface::ENTITY => array(Event::TYPE_SAVE),
        Category::ENTITY => array(Event::TYPE_SAVE)
    );

    /**
     * Related Configuration Settings for match
     *
     * @var string[]
     */
    protected $_relatedConfigSettings = array(ModelFulltext::XML_PATH_CATALOG_SEARCH_TYPE);

    /**
     * Retrieve Fulltext Search instance
     *
     * @return ModelFulltext
     */
    protected function _getIndexer()
    {
        return $this->_catalogSearchFulltext;
    }

    /**
     * Retrieve Indexer name
     *
     * @return string
     */
    public function getName()
    {
        return __('Catalog Search');
    }

    /**
     * Retrieve Indexer description
     *
     * @return string
     */
    public function getDescription()
    {
        return __('Rebuild Catalog product fulltext search index');
    }

    /**
     * Check if event can be matched by process
     * Overwrote for check is flat catalog product is enabled and specific save
     * attribute, store, store_group
     *
     * @param Event $event
     * @return bool
     */
    public function matchEvent(Event $event)
    {
        $data = $event->getNewData();
        if (isset($data[self::EVENT_MATCH_RESULT_KEY])) {
            return $data[self::EVENT_MATCH_RESULT_KEY];
        }

        $entity = $event->getEntity();
        if ($entity == Attribute::ENTITY) {
            /* @var $attribute Attribute */
            $attribute = $event->getDataObject();

            if (!$attribute) {
                $result = false;
            } elseif ($event->getType() == Event::TYPE_SAVE) {
                $result = $attribute->dataHasChangedFor('is_searchable');
            } elseif ($event->getType() == Event::TYPE_DELETE) {
                $result = $attribute->getIsSearchable();
            } else {
                $result = false;
            }
        } else if ($entity == Store::ENTITY) {
            if ($event->getType() == Event::TYPE_DELETE) {
                $result = true;
            } else {
                /* @var $store Store */
                $store = $event->getDataObject();
                if ($store && $store->isObjectNew()) {
                    $result = true;
                } else {
                    $result = false;
                }
            }
        } else if ($entity == Group::ENTITY) {
            /* @var $storeGroup Group */
            $storeGroup = $event->getDataObject();
            if ($storeGroup && $storeGroup->dataHasChangedFor('website_id')) {
                $result = true;
            } else {
                $result = false;
            }
        } else if ($entity == ValueInterface::ENTITY) {
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
     * @param Event $event
     * @return void
     */
    protected function _registerEvent(Event $event)
    {
        $event->addNewData(self::EVENT_MATCH_RESULT_KEY, true);
        switch ($event->getEntity()) {
            case Product::ENTITY:
                $this->_registerCatalogProductEvent($event);
                break;

            case ValueInterface::ENTITY:
            case Store::ENTITY:
            case Attribute::ENTITY:
            case Group::ENTITY:
                $event->addNewData('catalogsearch_fulltext_skip_call_event_handler', true);
                $process = $event->getProcess();
                $process->changeStatus(Process::STATUS_REQUIRE_REINDEX);
                break;
            case Category::ENTITY:
                $this->_registerCatalogCategoryEvent($event);
                break;
            default:
                break;
        }
    }

    /**
     * Get data required for category'es products reindex
     *
     * @param Event $event
     * @return $this
     */
    protected function _registerCatalogCategoryEvent(Event $event)
    {
        switch ($event->getType()) {
            case Event::TYPE_SAVE:
                /* @var $category Category */
                $category = $event->getDataObject();
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
            default:
                break;
        }

        return $this;
    }

    /**
     * Register data required by catatalog product process in event object
     *
     * @param Event $event
     * @return $this
     */
    protected function _registerCatalogProductEvent(Event $event)
    {
        switch ($event->getType()) {
            case Event::TYPE_SAVE:
                /* @var $product Product */
                $product = $event->getDataObject();

                $event->addNewData('catalogsearch_update_product_id', $product->getId());
                break;
            case Event::TYPE_DELETE:
                /* @var $product Product */
                $product = $event->getDataObject();

                $event->addNewData('catalogsearch_delete_product_id', $product->getId());
                break;
            case Event::TYPE_MASS_ACTION:
                /* @var $actionObject \Magento\Object */
                $actionObject = $event->getDataObject();

                $reindexData = array();
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
            default:
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
            /** @var $attributeCollection \Magento\Catalog\Model\Resource\Product\Attribute\Collection */
            $attributeCollection = $this->_attributeCollectionFactory->create();
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
        $product = $this->_productFactory->create()->load($productId);
        return $product->isComposite();
    }

    /**
     * Process event
     *
     * @param Event $event
     * @return void
     */
    protected function _processEvent(Event $event)
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

            $this->_getIndexer()->cleanIndex(null, $productId)->getResource()->resetSearchResults(null, $productId);
        } elseif (!empty($data['catalogsearch_update_product_id'])) {
            $productId = $data['catalogsearch_update_product_id'];
            $productIds = array($productId);

            if (!$this->_isProductComposite($productId)) {
                $parentIds = $this->_getResource()->getRelationsByChild($productId);
                if (!empty($parentIds)) {
                    $productIds = array_merge($productIds, $parentIds);
                }
            }

            $this->_getIndexer()->rebuildIndex(null, $productIds);
        } else if (!empty($data['catalogsearch_product_ids'])) {
            // mass action
            $productIds = $data['catalogsearch_product_ids'];

            if (!empty($data['catalogsearch_website_ids'])) {
                $websiteIds = $data['catalogsearch_website_ids'];
                $actionType = $data['catalogsearch_action_type'];

                foreach ($websiteIds as $websiteId) {
                    foreach ($this->_storeManager->getWebsite($websiteId)->getStoreIds() as $storeId) {
                        if ($actionType == 'remove') {
                            $this->_getIndexer()
                                ->cleanIndex($storeId, $productIds)
                                ->getResource()->resetSearchResults($storeId, $productIds);
                        } elseif ($actionType == 'add') {
                            $this->_getIndexer()
                                ->rebuildIndex($storeId, $productIds);
                        }
                    }
                }
            }
            if (isset($data['catalogsearch_status'])) {
                $status = $data['catalogsearch_status'];
                if ($status == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) {
                    $this->_getIndexer()->rebuildIndex(null, $productIds);
                } else {
                    $this->_getIndexer()->cleanIndex(
                        null,
                        $productIds
                    )->getResource()->resetSearchResults(
                        null,
                        $productIds
                    );
                }
            }
            if (isset($data['catalogsearch_force_reindex'])) {
                $this->_getIndexer()->rebuildIndex(null, $productIds)->resetSearchResults();
            }
        } else if (isset($data['catalogsearch_category_update_product_ids'])) {
            $productIds = $data['catalogsearch_category_update_product_ids'];
            $categoryIds = $data['catalogsearch_category_update_category_ids'];

            $this->_getIndexer()->updateCategoryIndex($productIds, $categoryIds);
        }
    }

    /**
     * Rebuild all index data
     *
     * @return void
     * @throws \Exception
     */
    public function reindexAll()
    {
        $resourceModel = $this->_getIndexer()->getResource();
        $resourceModel->beginTransaction();
        try {
            $this->_getIndexer()->rebuildIndex();
            $resourceModel->commit();
        } catch (\Exception $e) {
            $resourceModel->rollBack();
            throw $e;
        }
    }
}
