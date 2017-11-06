<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider;

use Magento\CatalogInventory\Model\Configuration as CatalogInventoryConfiguration;
use Magento\CatalogInventory\Model\Stock;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Request\BucketInterface;

/**
 *  Class for query building for Magento\CatalogSearch\Model\Adapter\Mysql\Aggregation\DataProvider.
 */
class QueryBuilder
{
    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @var CatalogInventoryConfiguration
     */
    private $inventoryConfig;

    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @param ResourceConnection $resource
     * @param ScopeResolverInterface $scopeResolver
     * @param CatalogInventoryConfiguration|null $inventoryConfig
     */
    public function __construct(
        ResourceConnection $resource,
        ScopeResolverInterface $scopeResolver,
        CatalogInventoryConfiguration $inventoryConfig = null
    ) {
        $this->resource = $resource;
        $this->scopeResolver = $scopeResolver;
        $this->inventoryConfig = $inventoryConfig ?: ObjectManager::getInstance()->get(
            CatalogInventoryConfiguration::class
        );
        $this->connection = $resource->getConnection();
    }

    /**
     * Build select.
     *
     * @param AbstractAttribute $attribute
     * @param string $tableName
     * @param int $currentScope
     * @param int $customerGroupId
     *
     * @return Select
     */
    public function build(
        AbstractAttribute $attribute,
        $tableName,
        $currentScope,
        $customerGroupId
    ) {
        $select = $this->getSelect();

        $select->joinInner(
            ['entities' => $tableName],
            'main_table.entity_id  = entities.entity_id',
            []
        );

        if ($attribute->getAttributeCode() === 'price') {
            /** @var \Magento\Store\Model\Store $store */
            $store = $this->scopeResolver->getScope($currentScope);
            if (!$store instanceof \Magento\Store\Model\Store) {
                throw new \RuntimeException('Illegal scope resolved');
            }

            $select = $this->buildIfPrice(
                $store->getWebsiteId(),
                $customerGroupId,
                $select
            );
        } else {
            $currentScopeId = $this->scopeResolver->getScope($currentScope)
                ->getId();

            $select = $this->buildIfNotPrice(
                $currentScopeId,
                $attribute,
                $select
            );
        }

        return $select;
    }

    /**
     * Build select if it is price attribute.
     *
     * @param int $websiteId
     * @param int $customerGroupId
     * @param Select $select
     *
     * @return Select
     */
    private function buildIfPrice(
        $websiteId,
        $customerGroupId,
        Select $select
    ) {
        $table = $this->resource->getTableName('catalog_product_index_price');
        $select->from(['main_table' => $table], null)
            ->columns([BucketInterface::FIELD_VALUE => 'main_table.min_price'])
            ->where('main_table.customer_group_id = ?', $customerGroupId)
            ->where('main_table.website_id = ?', $websiteId);

        return $select;
    }

    /**
     * Build select if it is not price attribute.
     *
     * @param int $currentScopeId
     * @param AbstractAttribute $attribute
     * @param Select $select
     *
     * @return Select
     */
    private function buildIfNotPrice(
        $currentScopeId,
        AbstractAttribute $attribute,
        Select $select
    ) {
        $table = $this->resource->getTableName(
            'catalog_product_index_eav' . ($attribute->getBackendType() === 'decimal' ? '_decimal' : '')
        );
        $subSelect = $select;
        $subSelect->from(['main_table' => $table], ['main_table.entity_id', 'main_table.value'])
            ->distinct()
            ->joinLeft(
                ['stock_index' => $this->resource->getTableName('cataloginventory_stock_status')],
                'main_table.source_id = stock_index.product_id',
                []
            )
            ->where('main_table.attribute_id = ?', $attribute->getAttributeId())
            ->where('main_table.store_id = ? ', $currentScopeId);

        if (!$this->inventoryConfig->isShowOutOfStock($currentScopeId)) {
            $subSelect->where('stock_index.stock_status = ?', Stock::STOCK_IN_STOCK);
        }

        $parentSelect = $this->getSelect();
        $parentSelect->from(['main_table' => $subSelect], ['main_table.value']);
        $select = $parentSelect;

        return $select;
    }

    /**
     * Get empty select.
     *
     * @return Select
     */
    private function getSelect()
    {
        return $this->connection->select();
    }
}
