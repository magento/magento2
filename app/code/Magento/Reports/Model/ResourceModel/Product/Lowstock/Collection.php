<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product Low Stock Report Collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Reports\Model\ResourceModel\Product\Lowstock;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends \Magento\Reports\Model\ResourceModel\Product\Collection
{
    /**
     * Flag about is joined CatalogInventory Stock Item
     *
     * @var bool
     */
    protected $_inventoryItemJoined = false;

    /**
     * Alias for CatalogInventory Stock Item Table
     *
     * @var string
     */
    protected $_inventoryItemTableAlias = 'lowstock_inventory_item';

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock\Item
     */
    protected $_itemResource;

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
     * @param \Magento\Catalog\Model\ResourceModel\Product $product
     * @param \Magento\Reports\Model\Event\TypeFactory $eventTypeFactory
     * @param \Magento\Catalog\Model\Product\Type $productType
     * @param \Magento\Quote\Model\ResourceModel\Quote\Collection $quoteResource
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\CatalogInventory\Model\ResourceModel\Stock\Item $itemResource
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
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
        \Magento\Catalog\Model\ResourceModel\Product $product,
        \Magento\Reports\Model\Event\TypeFactory $eventTypeFactory,
        \Magento\Catalog\Model\Product\Type $productType,
        \Magento\Quote\Model\ResourceModel\Quote\Collection $quoteResource,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\CatalogInventory\Model\ResourceModel\Stock\Item $itemResource,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null
    ) {
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
            $product,
            $eventTypeFactory,
            $productType,
            $quoteResource,
            $connection
        );
        $this->stockRegistry = $stockRegistry;
        $this->stockConfiguration = $stockConfiguration;
        $this->_itemResource = $itemResource;
    }

    /**
     * Retrieve CatalogInventory Stock Item Table
     *
     * @return string
     */
    protected function _getInventoryItemTable()
    {
        return $this->_itemResource->getMainTable();
    }

    /**
     * Retrieve CatalogInventory Stock Item Table Id field name
     *
     * @return string
     */
    protected function _getInventoryItemIdField()
    {
        return $this->_itemResource->getIdFieldName();
    }

    /**
     * Retrieve alias for CatalogInventory Stock Item Table
     *
     * @return string
     */
    protected function _getInventoryItemTableAlias()
    {
        return $this->_inventoryItemTableAlias;
    }

    /**
     * Add catalog inventory stock item field to select
     *
     * @param string $field
     * @param string $alias
     * @return $this
     */
    protected function _addInventoryItemFieldToSelect($field, $alias = null)
    {
        if (empty($alias)) {
            $alias = $field;
        }

        if (isset($this->_joinFields[$alias])) {
            return $this;
        }

        $this->_joinFields[$alias] = ['table' => $this->_getInventoryItemTableAlias(), 'field' => $field];

        $this->getSelect()->columns([$alias => $field], $this->_getInventoryItemTableAlias());
        return $this;
    }

    /**
     * Retrieve catalog inventory stock item field correlation name
     *
     * @param string $field
     * @return string
     */
    protected function _getInventoryItemField($field)
    {
        return sprintf('%s.%s', $this->_getInventoryItemTableAlias(), $field);
    }

    /**
     * Join catalog inventory stock item table for further stock_item values filters
     *
     * @param array $fields
     * @return $this
     */
    public function joinInventoryItem($fields = [])
    {
        if (!$this->_inventoryItemJoined) {
            $this->getSelect()->join(
                [$this->_getInventoryItemTableAlias() => $this->_getInventoryItemTable()],
                sprintf(
                    'e.%s = %s.product_id',
                    $this->getEntity()->getEntityIdField(),
                    $this->_getInventoryItemTableAlias()
                ),
                []
            );
            $this->_inventoryItemJoined = true;
        }

        if (!is_array($fields)) {
            if (empty($fields)) {
                $fields = [];
            } else {
                $fields = [$fields];
            }
        }

        foreach ($fields as $alias => $field) {
            if (!is_string($alias)) {
                $alias = null;
            }
            $this->_addInventoryItemFieldToSelect($field, $alias);
        }

        return $this;
    }

    /**
     * Add filter by product type(s)
     *
     * @param array|string $typeFilter
     * @return $this
     */
    public function filterByProductType($typeFilter)
    {
        if (!is_string($typeFilter) && !is_array($typeFilter)) {
            new \Magento\Framework\Exception\LocalizedException(__('The product type filter specified is incorrect.'));
        }
        $this->addAttributeToFilter('type_id', $typeFilter);
        return $this;
    }

    /**
     * Add filter by product types from config - only types which have QTY parameter
     *
     * @return $this
     */
    public function filterByIsQtyProductTypes()
    {
        $this->filterByProductType(array_keys(array_filter($this->stockConfiguration->getIsQtyTypeIds())));
        return $this;
    }

    /**
     * Add Use Manage Stock Condition to collection
     *
     * @param null|int $storeId
     * @return $this
     */
    public function useManageStockFilter($storeId = null)
    {
        $this->joinInventoryItem();
        $manageStockExpr = $this->getConnection()->getCheckSql(
            $this->_getInventoryItemField('use_config_manage_stock') . ' = 1',
            (int)$this->stockConfiguration->getManageStock($storeId),
            $this->_getInventoryItemField('manage_stock')
        );
        $this->getSelect()->where($manageStockExpr . ' = ?', 1);
        return $this;
    }

    /**
     * Add Notify Stock Qty Condition to collection
     *
     * @param null|int $storeId
     * @return $this
     */
    public function useNotifyStockQtyFilter($storeId = null)
    {
        $this->joinInventoryItem(['qty']);
        $notifyStockExpr = $this->getConnection()->getCheckSql(
            $this->_getInventoryItemField('use_config_notify_stock_qty') . ' = 1',
            (int)$this->stockConfiguration->getNotifyStockQty($storeId),
            $this->_getInventoryItemField('notify_stock_qty')
        );
        $this->getSelect()->where($this->_getInventoryItemField('qty') . ' < ?', $notifyStockExpr);
        return $this;
    }
}
