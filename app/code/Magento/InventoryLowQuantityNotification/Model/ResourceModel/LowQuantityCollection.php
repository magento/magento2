<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotification\Model\ResourceModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Inventory\Model\ResourceModel\Source;
use Magento\Inventory\Model\ResourceModel\SourceItem as SourceItemResourceModel;
use Magento\Inventory\Model\SourceItem as SourceItemModel;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryConfigurationApi\Model\GetAllowedProductTypesForSourceItemManagementInterface;
use Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterface;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class LowQuantityCollection extends AbstractCollection
{
    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var GetAllowedProductTypesForSourceItemManagementInterface
     */
    private $getAllowedProductTypesForSourceItemManagement;

    /**
     * @var AttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var int
     */
    private $filterStoreId;

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param AttributeRepositoryInterface $attributeRepository
     * @param StockConfigurationInterface $stockConfiguration
     * @param GetAllowedProductTypesForSourceItemManagementInterface $getAllowedProductTypesForSourceItemManagement
     * @param MetadataPool $metadataPool
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        AttributeRepositoryInterface $attributeRepository,
        StockConfigurationInterface $stockConfiguration,
        GetAllowedProductTypesForSourceItemManagementInterface $getAllowedProductTypesForSourceItemManagement,
        MetadataPool $metadataPool,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );

        $this->attributeRepository = $attributeRepository;
        $this->stockConfiguration = $stockConfiguration;
        $this->getAllowedProductTypesForSourceItemManagement = $getAllowedProductTypesForSourceItemManagement;
        $this->metadataPool = $metadataPool;
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(SourceItemModel::class, SourceItemResourceModel::class);

        $this->addFilterToMap('source_code', 'main_table.source_code');
        $this->addFilterToMap('sku', 'main_table.sku');
        $this->addFilterToMap('product_name', 'product_entity_varchar.value');
    }

    /**
     * @param int $storeId
     * @return void
     */
    public function addStoreFilter(int $storeId)
    {
        $this->filterStoreId = $storeId;
    }

    /**
     * @inheritdoc
     */
    protected function _renderFilters()
    {
        if (false === $this->_isFiltersRendered) {
            $this->joinInventoryConfiguration();
            $this->joinCatalogProduct();

            $this->addProductTypeFilter();
            $this->addNotifyStockQtyFilter();
            $this->addEnabledSourceFilter();
            $this->addSourceItemInStockFilter();
        }
        return parent::_renderFilters();
    }

    /**
     * @inheritdoc
     */
    protected function _renderOrders()
    {
        if (false === $this->_isOrdersRendered) {
            $this->setOrder(SourceItemInterface::QUANTITY, self::SORT_ORDER_ASC);
        }
        return parent::_renderOrders();
    }

    /**
     * joinCatalogProduct depends on dynamic condition 'filterStoreId'
     *
     * @return void
     */
    private function joinCatalogProduct()
    {
        $productEntityTable = $this->getTable('catalog_product_entity');
        $productEavVarcharTable = $this->getTable('catalog_product_entity_varchar');
        $nameAttribute = $this->attributeRepository->get('catalog_product', 'name');

        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $metadata->getLinkField();

        $this->getSelect()->joinInner(
            ['product_entity' => $productEntityTable],
            'main_table.' . SourceItemInterface::SKU . ' = product_entity.' . ProductInterface::SKU,
            []
        );

        $this->getSelect()->joinInner(
            ['product_entity_varchar' => $productEavVarcharTable],
            'product_entity_varchar.' . $linkField . ' = product_entity.' . $linkField . ' ' .
            'AND product_entity_varchar.store_id = ' . Store::DEFAULT_STORE_ID. ' ' .
            'AND product_entity_varchar.attribute_id = ' . (int)$nameAttribute->getAttributeId(),
            []
        );

        if (null !== $this->filterStoreId) {
            $this->getSelect()->joinLeft(
                ['product_entity_varchar_store' => $productEavVarcharTable],
                'product_entity_varchar_store.' . $linkField . ' = product_entity.' . $linkField . ' ' .
                'AND product_entity_varchar_store.store_id = ' . (int)$this->filterStoreId . ' ' .
                'AND product_entity_varchar_store.attribute_id = ' . (int)$nameAttribute->getAttributeId(),
                [
                    'product_name' => $this->getConnection()->getIfNullSql(
                        'product_entity_varchar_store.value',
                        'product_entity_varchar.value'
                    )
                ]
            );
        } else {
            $this->getSelect()->columns(['product_name' => 'product_entity_varchar.value']);
        }
    }

    /**
     * @return void
     */
    private function joinInventoryConfiguration()
    {
        $sourceItemConfigurationTable = $this->getTable('inventory_low_stock_notification_configuration');

        $this->getSelect()->joinInner(
            ['notification_configuration' => $sourceItemConfigurationTable],
            sprintf(
                'main_table.%s = notification_configuration.%s AND main_table.%s = notification_configuration.%s',
                SourceItemInterface::SKU,
                SourceItemConfigurationInterface::SKU,
                SourceItemInterface::SOURCE_CODE,
                SourceItemConfigurationInterface::SOURCE_CODE
            ),
            []
        );
    }

    /**
     * @return void
     */
    private function addProductTypeFilter()
    {
        $this->addFieldToFilter(
            'product_entity.type_id',
            $this->getAllowedProductTypesForSourceItemManagement->execute()
        );
    }

    /**
     * @return void
     */
    private function addNotifyStockQtyFilter()
    {
        $notifyStockExpression = $this->getConnection()->getIfNullSql(
            'notification_configuration.' . SourceItemConfigurationInterface::INVENTORY_NOTIFY_QTY,
            (float)$this->stockConfiguration->getNotifyStockQty()
        );

        $this->getSelect()->where(
            SourceItemInterface::QUANTITY . ' < ?',
            $notifyStockExpression
        );
    }

    /**
     * @return void
     */
    private function addEnabledSourceFilter()
    {
        $this->getSelect()->joinInner(
            ['inventory_source' => $this->getTable(Source::TABLE_NAME_SOURCE)],
            sprintf(
                'inventory_source.%s = 1 AND inventory_source.%s = main_table.%s',
                SourceInterface::ENABLED,
                SourceInterface::SOURCE_CODE,
                SourceItemInterface::SOURCE_CODE
            ),
            []
        );
    }

    /**
     * @return void
     */
    private function addSourceItemInStockFilter()
    {
        $this->addFieldToFilter('main_table.status', SourceItemInterface::STATUS_IN_STOCK);
    }
}
