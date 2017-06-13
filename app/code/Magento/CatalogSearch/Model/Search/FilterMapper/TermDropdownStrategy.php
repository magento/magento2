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
use Magento\Framework\App\ObjectManager;

/**
 * This strategy handles attributes which comply with two criteria:
 *   - The filter for dropdown or multi-select attribute
 *   - The filter is Term filter
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TermDropdownStrategy implements FilterStrategyInterface
{
    /**
     * @var AliasResolver
     */
    private $aliasResolver;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Indexer\Model\ResourceModel\FrontendResource
     */
    private $frontendResource;

    /**
     * @var \Magento\Indexer\Model\ResourceModel\FrontendResource
     */
    private $indexerStockFrontendResource;

    /**
     * @param StoreManagerInterface $storeManager
     * @param ResourceConnection $resourceConnection
     * @param EavConfig $eavConfig
     * @param ScopeConfigInterface $scopeConfig
     * @param AliasResolver $aliasResolver
     * @param \Magento\Indexer\Model\ResourceModel\FrontendResource|null $frontendResource
     * @param null|\Magento\Indexer\Model\ResourceModel\FrontendResource $indexerStockFrontendResource
     * @SuppressWarnings(Magento.TypeDuplication)
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        ResourceConnection $resourceConnection,
        EavConfig $eavConfig,
        ScopeConfigInterface $scopeConfig,
        AliasResolver $aliasResolver,
        \Magento\Indexer\Model\ResourceModel\FrontendResource $frontendResource = null,
        \Magento\Indexer\Model\ResourceModel\FrontendResource $indexerStockFrontendResource = null
    ) {
        $this->storeManager = $storeManager;
        $this->resourceConnection = $resourceConnection;
        $this->eavConfig = $eavConfig;
        $this->scopeConfig = $scopeConfig;
        $this->aliasResolver = $aliasResolver;
        $this->frontendResource = $frontendResource ?: ObjectManager::getInstance()
            ->get(\Magento\Catalog\Model\ResourceModel\Product\Indexer\Eav\FrontendResource::class);
        $this->indexerStockFrontendResource = $indexerStockFrontendResource ?: ObjectManager::getInstance()
            ->get(\Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock\FrontendResource::class);
    }

    /**
     * {@inheritDoc}
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
            $this->storeManager->getStore()->getId()
        );
        $select->joinLeft(
            [$alias => $this->frontendResource->getMainTable()],
            $joinCondition,
            []
        );
        if ($this->isAddStockFilter()) {
            $stockAlias = $alias . AliasResolver::STOCK_FILTER_SUFFIX;
            $select->joinLeft(
                [
                    $stockAlias => $this->indexerStockFrontendResource->getMainTable(),
                ],
                sprintf('%2$s.product_id = %1$s.source_id', $alias, $stockAlias),
                []
            );
        }

        return true;
    }

    /**
     * @param string $field
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAttributeByCode($field)
    {
        return $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $field);
    }

    /**
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
