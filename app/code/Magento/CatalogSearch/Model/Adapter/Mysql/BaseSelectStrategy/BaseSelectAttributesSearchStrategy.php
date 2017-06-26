<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Adapter\Mysql\BaseSelectStrategy;

use Magento\CatalogSearch\Model\Search\BaseSelectStrategy\BaseSelectStrategyInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\CatalogSearch\Model\Search\SelectContainer\SelectContainer;

/**
 * Class BaseSelectAttributesSearchStrategy
 * This class represents strategy for building base select query for search request
 *
 * The main idea of this strategy is using eav index table as main table for query
 * in case when search request requires search by attributes
 */
class BaseSelectAttributesSearchStrategy implements BaseSelectStrategyInterface
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var IndexScopeResolver
     */
    private $scopeResolver;

    /**
     * @param ResourceConnection $resource
     * @param StoreManagerInterface $storeManager
     * @param IndexScopeResolver $scopeResolver
     */
    public function __construct(
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        IndexScopeResolver $scopeResolver
    ) {
        $this->resource = $resource;
        $this->storeManager = $storeManager;
        $this->scopeResolver = $scopeResolver;
    }

    /**
     * Creates base select query that can be populated with additional filters
     *
     * @param SelectContainer $selectContainer
     * @return SelectContainer
     * @throws \DomainException
     */
    public function createBaseSelect(SelectContainer $selectContainer)
    {
        $select = $this->resource->getConnection()->select();
        $mainTableAlias = $selectContainer->isFullTextSearchRequired() ? 'eav_index' : 'search_index';

        $select->distinct()
            ->from(
                [$mainTableAlias => $this->resource->getTableName('catalog_product_index_eav')],
                ['entity_id' => 'entity_id']
            )->where(
                $this->resource->getConnection()->quoteInto(
                    sprintf('%s.store_id = ?', $mainTableAlias),
                    $this->storeManager->getStore()->getId()
                )
            );

        if ($selectContainer->isFullTextSearchRequired()) {
            $tableName = $this->scopeResolver->resolve(
                $selectContainer->getUsedIndex(),
                $selectContainer->getDimensions()
            );

            $select->joinInner(
                ['search_index' => $tableName],
                'eav_index.entity_id = search_index.entity_id',
                []
            )->joinInner(
                ['cea' => $this->resource->getTableName('catalog_eav_attribute')],
                'search_index.attribute_id = cea.attribute_id',
                []
            );
        }

        $selectContainer = $selectContainer->updateSelect($select);
        return $selectContainer;
    }
}
