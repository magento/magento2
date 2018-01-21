<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryConfiguration\Model\ResourceModel\Product\Lowstock;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Inventory\Model\ResourceModel\SourceItem\Collection as SourceItemCollection;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryConfiguration\Setup\Operation\CreateSourceConfigurationTable;
use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterface;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Collection extends SourceItemCollection
{
    /**
     * @var StockConfigurationInterface
     */
    protected $stockConfiguration;

    /**
     * @var AttributeRepositoryInterface
     */
    protected $attributeRepositoryInterface;

    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        AttributeRepositoryInterface $attributeRepositoryInterface,
        StockConfigurationInterface $stockConfiguration,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->attributeRepositoryInterface = $attributeRepositoryInterface;
        $this->stockConfiguration = $stockConfiguration;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function _initSelect()
    {
        $this->addFilterToMap('inventory_source_code', 'main_table.source_code');
        $this->addFilterToMap('source_item_sku', 'main_table.sku');
        $this->addFilterToMap('product_name', 'catalog_product_entity_varchar.value');

        return parent::_initSelect();
    }

    /**
     * Join tables with product information
     *
     * @return $this
     */
    public function joinCatalogProduct()
    {
        $productEntityTable = $this->getTable('catalog_product_entity');
        $productEavVarcharTable = $this->getTable('catalog_product_entity_varchar');
        $nameAttribute = $this->attributeRepositoryInterface->get('catalog_product', 'name');

        $this->getSelect()->joinLeft(
            $productEntityTable,
            sprintf(
                'main_table.%s = %s.' . SourceItemInterface::SKU,
                ProductInterface::SKU,
                $productEntityTable
            ),
            []
        );

        /* Join product name */
        $joinExpression = sprintf(
            $productEavVarcharTable . '.entity_id = %s.entity_id',
            $productEntityTable
        );

        $joinExpression .= sprintf(
            ' AND ' . $productEavVarcharTable . '.attribute_id = %s',
            $nameAttribute->getAttributeId()
        );

        $this->getSelect()->joinLeft(
            $productEavVarcharTable,
            $joinExpression,
            ['value as name']
        );

        return $this;
    }

    /**
     * Join inventory configuration table
     *
     * @return $this
     */
    public function joinInventoryConfiguration()
    {
        /* Join by sku field */
        $joinExpression = sprintf(
            'main_table.%s = %s.' . SourceItemConfigurationInterface::SKU,
            SourceItemInterface::SKU,
            CreateSourceConfigurationTable::TABLE_NAME_SOURCE_ITEM_CONFIGURATION
        );

        /* Join by source_code field */
        $joinExpression .= sprintf(
            ' AND main_table.%s = %s.' . SourceItemConfigurationInterface::SOURCE_CODE,
            SourceItemInterface::SOURCE_CODE,
            CreateSourceConfigurationTable::TABLE_NAME_SOURCE_ITEM_CONFIGURATION
        );

        $this->getSelect()->joinLeft(
            CreateSourceConfigurationTable::TABLE_NAME_SOURCE_ITEM_CONFIGURATION,
            $joinExpression,
            []
        );

        return $this;
    }

    /**
     * Add filter by product type(s)
     *
     * @param array|string $typeFilter
     * @throws LocalizedException
     * @return $this
     */
    public function filterByProductType($typeFilter)
    {
        if (!is_string($typeFilter) && !is_array($typeFilter)) {
            throw new LocalizedException(__('The product type filter specified is incorrect.'));
        }
        $this->addFieldToFilter('type_id', $typeFilter);

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
     * Add Notify Stock Qty Condition to collection
     *
     * @param null $storeId
     * @return $this
     */
    public function useNotifyStockQtyFilter($storeId = null)
    {
        $notifyQtyField = CreateSourceConfigurationTable::TABLE_NAME_SOURCE_ITEM_CONFIGURATION .
            '.' . SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY;

        $notifyStockExpression = $this->getConnection()->getIfNullSql(
            $notifyQtyField,
            (int)$this->stockConfiguration->getNotifyStockQty($storeId)
        );

        $this->getSelect()->where(
            SourceItemInterface::QUANTITY . ' < ?',
            $notifyStockExpression
        );
        
        return $this;
    }
}
