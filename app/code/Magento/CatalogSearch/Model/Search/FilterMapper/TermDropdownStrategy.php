<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\FilterMapper;

use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * This strategy handles attributes which comply with two criteria:
 *   - The filter for dropdown or multi-select attribute
 *   - The filter is Term filter
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TermDropdownStrategy implements FilterStrategyInterface
{
    /**
     * Resolving table alias for Search Request filter.
     *
     * @var AliasResolver
     */
    private $aliasResolver;

    /**
     * Store manager.
     *
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Eav attributes config.
     *
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * Resource connection.
     *
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * Scope config.
     *
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ResourceConnection $resourceConnection
     * @param EavConfig $eavConfig
     * @param ScopeConfigInterface $scopeConfig
     * @param AliasResolver $aliasResolver
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ResourceConnection $resourceConnection,
        EavConfig $eavConfig,
        ScopeConfigInterface $scopeConfig,
        AliasResolver $aliasResolver
    ) {
        $this->storeManager = $storeManager;
        $this->resourceConnection = $resourceConnection;
        $this->eavConfig = $eavConfig;
        $this->scopeConfig = $scopeConfig;
        $this->aliasResolver = $aliasResolver;
    }

    /**
     * Applies filter.
     *
     * @param \Magento\Framework\Search\Request\FilterInterface $filter
     * @param \Magento\Framework\DB\Select $select
     *
     * @return bool is filter was applied
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function apply(
        \Magento\Framework\Search\Request\FilterInterface $filter,
        \Magento\Framework\DB\Select $select
    ) {
        $alias = $this->aliasResolver->getAlias($filter);
        $attribute = $this->getAttributeByCode($filter->getField());
        $joinCondition = sprintf(
            'search_index.entity_id = %1$s.entity_id AND %1$s.attribute_id = %2$d AND %1$s.store_id = %3$d',
            $alias,
            $attribute->getId(),
            $this->storeManager->getWebsite()->getId()
        );
        $select->joinLeft(
            [$alias => $this->resourceConnection->getTableName('catalog_product_index_eav')],
            $joinCondition,
            []
        );

        if ($this->isAddStockFilter()) {
            $stockAlias = $alias . AliasResolver::STOCK_FILTER_SUFFIX;
            $select->joinLeft(
                [$stockAlias => $this->resourceConnection->getTableName('cataloginventory_stock_status')],
                sprintf('%2$s.product_id = %1$s.source_id', $alias, $stockAlias),
                []
            );
        }

        return true;
    }

    /**
     * Returns attribute by attribute code.
     *
     * @param string $field
     *
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAttributeByCode($field)
    {
        return $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field);
    }

    /**
     * Check if it is necessary to show out of stock products.
     *
     * @return bool
     */
    private function isAddStockFilter()
    {
        $isShowOutOfStock = $this->scopeConfig->isSetFlag(
            'cataloginventory/options/show_out_of_stock',
            ScopeInterface::SCOPE_STORE
        );

        return false === $isShowOutOfStock;
    }
}
