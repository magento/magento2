<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Adapter\Mysql\BaseSelectStrategy;

use Magento\CatalogSearch\Model\Search\BaseSelectStrategy\BaseSelectStrategyInterface;
use Magento\CatalogSearch\Model\Search\SelectContainer\SelectContainer;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Framework\App\ResourceConnection;

/**
 * Class BaseSelectFullTextSearchStrategy
 * This class represents strategy for building base select query for search request
 *
 * The main idea of this strategy is using fulltext search index table as main table for query
 * in case when search request does not requires any search by attributes
 */
class BaseSelectFullTextSearchStrategy implements BaseSelectStrategyInterface
{
    /**
     * @var IndexScopeResolver
     */
    private $scopeResolver;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @param ResourceConnection $resource
     * @param IndexScopeResolver $scopeResolver
     */
    public function __construct(
        ResourceConnection $resource,
        IndexScopeResolver $scopeResolver
    ) {
        $this->resource = $resource;
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

        $tableName = $this->scopeResolver->resolve(
            $selectContainer->getUsedIndex(),
            $selectContainer->getDimensions()
        );

        $select->from(
            ['search_index' => $tableName],
            ['entity_id' => 'entity_id']
        )->joinInner(
            ['cea' => $this->resource->getTableName('catalog_eav_attribute')],
            'search_index.attribute_id = cea.attribute_id',
            []
        );

        $selectContainer = $selectContainer->updateSelect($select);
        return $selectContainer;
    }
}
