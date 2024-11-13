<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product\Compare\Item;

use Magento\Customer\Model\Config\Share;
use Magento\Framework\App\ObjectManager;

/**
 * Catalog Product Compare Items Resource Collection
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @SuppressWarnings(PHPMD.LongVariable)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @since 100.0.2
 */
class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /**
     * Customer Filter
     *
     * @var int
     */
    protected $_customerId = 0;

    /**
     * Visitor Filter
     *
     * @var int
     */
    protected $_visitorId = 0;

    /**
     * List Id Filter
     *
     * @var int
     */
    protected $listId = 0;

    /**
     * Comparable attributes cache
     *
     * @var array
     */
    protected $_comparableAttributes;

    /**
     * @var \Magento\Catalog\Helper\Product\Compare
     */
    protected $_catalogProductCompare = null;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Compare\Item
     */
    protected $_catalogProductCompareItem;

    /**
     * @var Share
     */
    private $share;

    /**
     * Collection constructor.
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Eav\Model\EntityFactory $eavEntityFactory
     * @param \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Validator\UniversalFactory $universalFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Url $catalogUrl
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Customer\Api\GroupManagementInterface $groupManagement
     * @param \Magento\Catalog\Model\ResourceModel\Product\Compare\Item $catalogProductCompareItem
     * @param \Magento\Catalog\Helper\Product\Compare $catalogProductCompare
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param Share|null $share
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrl,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Catalog\Model\ResourceModel\Product\Compare\Item $catalogProductCompareItem,
        \Magento\Catalog\Helper\Product\Compare $catalogProductCompare,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        ?Share $share = null,
    ) {
        $this->_catalogProductCompareItem = $catalogProductCompareItem;
        $this->_catalogProductCompare = $catalogProductCompare;
        $this->share = $share ?? ObjectManager::getInstance()->get(Share::class);
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $moduleManager,
            $catalogProductFlatState,
            $scopeConfig,
            $productOptionFactory,
            $catalogUrl,
            $localeDate,
            $customerSession,
            $dateTime,
            $groupManagement,
            $connection
        );
    }

    /**
     * Initialize resources
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Magento\Catalog\Model\Product\Compare\Item::class,
            \Magento\Catalog\Model\ResourceModel\Product::class
        );
        $this->_initTables();
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        parent::_resetState();
        $this->_customerId = 0;
        $this->_visitorId = 0;
        $this->listId = 0;
        $this->_comparableAttributes = null;
    }

    /**
     * Set customer filter to collection
     *
     * @param int $customerId
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        $this->_customerId = (int)$customerId;
        $this->_addJoinToSelect();
        return $this;
    }

    /**
     * Set listId filter to collection
     *
     * @param int $listId
     *
     * @return $this
     */
    public function setListId(int $listId)
    {
        $this->listId = $listId;
        $this->_addJoinToSelect();
        return $this;
    }

    /**
     * Retrieve listId filter applied to collection
     *
     * @return int
     */
    public function getListId(): int
    {
        return (int)$this->listId;
    }

    /**
     * Set visitor filter to collection
     *
     * @param int $visitorId
     * @return $this
     */
    public function setVisitorId($visitorId)
    {
        $this->_visitorId = (int)$visitorId;
        $this->_addJoinToSelect();
        return $this;
    }

    /**
     * Retrieve customer filter applied to collection
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->_customerId;
    }

    /**
     * Retrieve visitor filter applied to collection
     *
     * @return int
     */
    public function getVisitorId()
    {
        return $this->_visitorId;
    }

    /**
     * Retrieve condition for join filters
     *
     * @return array
     */
    public function getConditionForJoin()
    {
        if ($this->getCustomerId()) {
            $conditions['customer_id'] = $this->getCustomerId();
            if (!$this->share->isGlobalScope()) {
                $conditions['store_id'] = $this->getStoreId();
            }
            return $conditions;
        }

        if ($this->getVisitorId()) {
            return ['visitor_id' => $this->getVisitorId(), 'store_id' => $this->getStoreId()];
        }

        if ($this->getListId()) {
            return ['list_id' => $this->getListId()];
        }

        return ['customer_id' => ['null' => true], 'visitor_id' => '0'];
    }

    /**
     * Add join to select
     *
     * @return $this
     */
    public function _addJoinToSelect()
    {
        $this->joinTable(
            ['t_compare' => 'catalog_compare_item'],
            'product_id=entity_id',
            [
                'product_id' => 'product_id',
                'customer_id' => 'customer_id',
                'visitor_id' => 'visitor_id',
                'item_store_id' => 'store_id',
                'catalog_compare_item_id' => 'catalog_compare_item_id'
            ],
            $this->getConditionForJoin()
        );

        $this->_productLimitationFilters['store_table'] = 't_compare';

        return $this;
    }

    /**
     * Get products ids by for compare list
     *
     * @param int $listId
     *
     * @return array
     */
    public function getProductsByListId(int $listId): array
    {
        $select = $this->getConnection()->select()->
        from(
            $this->getTable('catalog_compare_item'),
            'product_id'
        )->where(
            'list_id = ?',
            $listId
        );
        return $this->getConnection()->fetchCol($select);
    }

    /**
     * Set list_id for customer compare item
     *
     * @param int $listId
     * @param int $customerId
     */
    public function setListIdToCustomerCompareItems(int $listId, int $customerId)
    {
        foreach ($this->getCustomerCompareItems($customerId) as $itemId) {
            $this->getConnection()->update(
                $this->getTable('catalog_compare_item'),
                ['list_id' => $listId],
                ['catalog_compare_item_id = ?' => (int)$itemId]
            );
        }
    }

    /**
     * Remove compare list if customer compare list empty
     *
     * @param int|null $customerId
     */
    public function removeCompareList(?int $customerId)
    {
        if (empty($this->getCustomerCompareItems($customerId))) {
            $this->getConnection()->delete(
                $this->getTable('catalog_compare_list'),
                ['customer_id = ?' => $customerId]
            );
        }
    }

    /**
     * Get customer compare items
     *
     * @param int|null $customerId
     * @return array
     */
    private function getCustomerCompareItems(?int $customerId): array
    {
        if ($customerId) {
            $select = $this->getConnection()->select()->
            from(
                $this->getTable('catalog_compare_item')
            )->where(
                'customer_id = ?',
                $customerId
            );

            return $this->getConnection()->fetchCol($select);
        }

        return [];
    }

    /**
     * Retrieve comapre products attribute set ids
     *
     * @return array
     */
    protected function _getAttributeSetIds()
    {
        // prepare compare items table conditions
        $compareConds = ['compare.product_id=entity.entity_id'];
        if ($this->getCustomerId()) {
            $compareConds[] = $this->getConnection()->quoteInto('compare.customer_id = ?', $this->getCustomerId());
        } else {
            $compareConds[] = $this->getConnection()->quoteInto('compare.visitor_id = ?', $this->getVisitorId());
        }

        // prepare website filter
        $websiteId = (int)$this->_storeManager->getStore($this->getStoreId())->getWebsiteId();
        $websiteConds = [
            'website.product_id = entity.entity_id',
            $this->getConnection()->quoteInto('website.website_id = ?', $websiteId),
        ];

        // retrieve attribute sets
        $select = $this->getConnection()->select()->distinct(
            true
        )->from(
            ['entity' => $this->getEntity()->getEntityTable()],
            'attribute_set_id'
        )->join(
            ['website' => $this->getTable('catalog_product_website')],
            join(' AND ', $websiteConds),
            []
        )->join(
            ['compare' => $this->getTable('catalog_compare_item')],
            join(' AND ', $compareConds),
            []
        );
        return $this->getConnection()->fetchCol($select);
    }

    /**
     * Retrieve attribute ids by set ids
     *
     * @param array $setIds
     * @return array
     */
    protected function _getAttributeIdsBySetIds(array $setIds)
    {
        $select = $this->getConnection()->select()->distinct(
            true
        )->from(
            $this->getTable('eav_entity_attribute'),
            'attribute_id'
        )->where(
            'attribute_set_id IN(?)',
            $setIds
        );
        return $this->getConnection()->fetchCol($select);
    }

    /**
     * Retrieve Merged comparable attributes for compared product items
     *
     * @return array
     */
    public function getComparableAttributes()
    {
        if ($this->_comparableAttributes === null) {
            $this->_comparableAttributes = [];
            $setIds = $this->_getAttributeSetIds();
            if ($setIds) {
                $attributeIds = $this->_getAttributeIdsBySetIds($setIds);

                $select = $this->getConnection()->select()->from(
                    ['main_table' => $this->getTable('eav_attribute')]
                )->join(
                    ['additional_table' => $this->getTable('catalog_eav_attribute')],
                    'additional_table.attribute_id=main_table.attribute_id'
                )->joinLeft(
                    ['al' => $this->getTable('eav_attribute_label')],
                    'al.attribute_id = main_table.attribute_id AND al.store_id = ' . (int)$this->getStoreId(),
                    [
                        'store_label' => $this->getConnection()->getCheckSql(
                            'al.value IS NULL',
                            'main_table.frontend_label',
                            'al.value'
                        )
                    ]
                )->where(
                    'additional_table.is_comparable=?',
                    1
                )->where(
                    'main_table.attribute_id IN(?)',
                    $attributeIds
                );
                $attributesData = $this->getConnection()->fetchAll($select);
                if ($attributesData) {
                    $entityType = \Magento\Catalog\Model\Product::ENTITY;
                    $this->_eavConfig->importAttributesData($entityType, $attributesData);
                    foreach ($attributesData as $data) {
                        $attribute = $this->_eavConfig->getAttribute($entityType, $data['attribute_code']);
                        $this->_comparableAttributes[$attribute->getAttributeCode()] = $attribute;
                    }
                    unset($attributesData);
                }
            }
        }
        return $this->_comparableAttributes;
    }

    /**
     * Load Comparable attributes
     *
     * @return $this
     */
    public function loadComparableAttributes()
    {
        $comparableAttributes = $this->getComparableAttributes();
        $attributes = [];
        foreach ($comparableAttributes as $attribute) {
            $attributes[] = $attribute->getAttributeCode();
        }
        $this->addAttributeToSelect($attributes);

        return $this;
    }

    /**
     * Use product as collection item
     *
     * @return $this
     */
    public function useProductItem()
    {
        $this->setObject(\Magento\Catalog\Model\Product::class);

        $this->setFlag('url_data_object', true);
        $this->setFlag('do_not_use_category_id', true);

        return $this;
    }

    /**
     * Retrieve product ids from collection
     *
     * @return int[]
     */
    public function getProductIds()
    {
        $ids = [];
        foreach ($this->getItems() as $item) {
            $ids[] = $item->getProductId();
        }

        return $ids;
    }

    /**
     * Clear compare items by condition
     *
     * @return $this
     */
    public function clear()
    {
        $this->_catalogProductCompareItem->clearItems($this->getVisitorId(), $this->getCustomerId());
        $this->_eventManager->dispatch('catalog_product_compare_item_collection_clear');

        return $this;
    }

    /**
     * Retrieve is flat enabled flag
     *
     * Overwrite disable flat for compared item if required EAV resource
     *
     * @return bool
     */
    public function isEnabledFlat()
    {
        if (!$this->_catalogProductCompare->getAllowUsedFlat()) {
            return false;
        }
        return parent::isEnabledFlat();
    }
}
