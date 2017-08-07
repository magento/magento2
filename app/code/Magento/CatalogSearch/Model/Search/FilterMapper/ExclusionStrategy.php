<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\FilterMapper;

use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver;

/**
 * Strategy which processes exclusions from general rules
 * @since 2.2.0
 */
class ExclusionStrategy implements FilterStrategyInterface
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     * @since 2.2.0
     */
    private $resourceConnection;

    /**
     * @var AliasResolver
     * @since 2.2.0
     */
    private $aliasResolver;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.2.0
     */
    private $storeManager;

    /**
     * List of fields that can be processed by exclusion strategy
     * @var array
     * @since 2.2.0
     */
    private $validFields = ['price', 'category_ids'];

    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param AliasResolver $aliasResolver
     * @since 2.2.0
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        AliasResolver $aliasResolver
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->storeManager = $storeManager;
        $this->aliasResolver = $aliasResolver;
    }

    /**
     * {@inheritDoc}
     * @since 2.2.0
     */
    public function apply(
        \Magento\Framework\Search\Request\FilterInterface $filter,
        \Magento\Framework\DB\Select $select
    ) {
        if (!in_array($filter->getField(), $this->validFields, true)) {
            return false;
        }

        if ($filter->getField() === 'price') {
            return $this->applyPriceFilter($filter, $select);
        } elseif ($filter->getField() === 'category_ids') {
            return $this->applyCategoryFilter($filter, $select);
        }
    }

    /**
     * Applies filter bt price field
     *
     * @param \Magento\Framework\Search\Request\FilterInterface $filter
     * @param \Magento\Framework\DB\Select $select
     * @return bool
     * @throws \DomainException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.2.0
     */
    private function applyPriceFilter(
        \Magento\Framework\Search\Request\FilterInterface $filter,
        \Magento\Framework\DB\Select $select
    ) {
        $alias = $this->aliasResolver->getAlias($filter);
        $tableName = $this->resourceConnection->getTableName('catalog_product_index_price');
        $mainTableAlias = $this->extractTableAliasFromSelect($select);

        $select->joinInner(
            [
                $alias => $tableName
            ],
            $this->resourceConnection->getConnection()->quoteInto(
                sprintf('%s.entity_id = price_index.entity_id AND price_index.website_id = ?', $mainTableAlias),
                $this->storeManager->getWebsite()->getId()
            ),
            []
        );

        return true;
    }

    /**
     * Applies filter by category
     *
     * @param \Magento\Framework\Search\Request\FilterInterface $filter
     * @param \Magento\Framework\DB\Select $select
     * @return bool
     * @throws \DomainException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.2.0
     */
    private function applyCategoryFilter(
        \Magento\Framework\Search\Request\FilterInterface $filter,
        \Magento\Framework\DB\Select $select
    ) {
        $alias = $this->aliasResolver->getAlias($filter);
        $tableName = $this->resourceConnection->getTableName('catalog_category_product_index');
        $mainTableAlias = $this->extractTableAliasFromSelect($select);

        $select->joinInner(
            [
                $alias => $tableName
            ],
            $this->resourceConnection->getConnection()->quoteInto(
                sprintf(
                    '%s.entity_id = category_ids_index.product_id AND category_ids_index.store_id = ?',
                    $mainTableAlias
                ),
                $this->storeManager->getStore()->getId()
            ),
            []
        );

        return true;
    }

    /**
     * Extracts alias for table that is used in FROM clause in Select
     *
     * @param \Magento\Framework\DB\Select $select
     * @return string|null
     * @since 2.2.0
     */
    private function extractTableAliasFromSelect(\Magento\Framework\DB\Select $select)
    {
        $fromArr = array_filter(
            $select->getPart(\Magento\Framework\DB\Select::FROM),
            function ($fromPart) {
                return $fromPart['joinType'] === \Magento\Framework\DB\Select::FROM;
            }
        );

        return $fromArr ? array_keys($fromArr)[0] : null;
    }
}
