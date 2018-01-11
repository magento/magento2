<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Product Low Stock Report Collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
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
 * @api
 * @since 100.0.2
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

    protected function _initSelect()
    {
        $this->addFilterToMap('inventory_source_code', 'main_table.source_code');
        $this->addFilterToMap('source_item_sku', 'main_table.sku');
        $this->addFilterToMap('product_name', 'catalog_product_entity_varchar.value');

        return parent::_initSelect();
    }

    public function joinCatalogProduct()
    {
        $nameAttribute = $this->attributeRepositoryInterface->get('catalog_product', 'name');
        $this->getSelect()->joinLeft(
            'catalog_product_entity', // TODO: replace the hardcoded value
            sprintf(
                'main_table.%s = %s.' . SourceItemInterface::SKU,
                ProductInterface::SKU,
                'catalog_product_entity'
            ),
            []
        );

        /* Join product name */
        $joinExpression = sprintf(
            'catalog_product_entity_varchar.entity_id = %s.entity_id', // TODO: replace the hardcoded values
            'catalog_product_entity'
        );

        $joinExpression .= sprintf(
            ' AND catalog_product_entity_varchar.attribute_id = %s', // TODO: replace the hardcoded values
            $nameAttribute->getAttributeId()
        );

        $this->getSelect()->joinLeft(
            'catalog_product_entity_varchar', // TODO: replace the hardcoded value
            $joinExpression,
            ['value as name']
        );

        return $this;
    }

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
