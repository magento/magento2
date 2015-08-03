<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Resource;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\Search\Adapter\Mysql\IndexBuilderInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Search\Request\QueryInterface;
use Magento\Framework\Search\Request\QueryInterface as RequestQueryInterface;
use Magento\Framework\Search\RequestInterface;
use Magento\Indexer\Model\ScopeResolver\IndexScopeResolver;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Build base Query for Index
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class IndexBuilder implements IndexBuilderInterface
{
    /**
     * @var Resource
     */
    private $resource;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var IndexScopeResolver
     */
    private $scopeResolver;
    /**
     * @var ConditionManager
     */
    private $conditionManager;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param ScopeConfigInterface $config
     * @param StoreManagerInterface $storeManager
     * @param ConditionManager $conditionManager
     * @param IndexScopeResolver $scopeResolver
     */
    public function __construct(
        Resource $resource,
        ScopeConfigInterface $config,
        StoreManagerInterface $storeManager,
        ConditionManager $conditionManager,
        IndexScopeResolver $scopeResolver
    ) {
        $this->resource = $resource;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->conditionManager = $conditionManager;
        $this->scopeResolver = $scopeResolver;
    }

    /**
     * Build index query
     *
     * @param RequestInterface $request
     * @return Select
     */
    public function build(RequestInterface $request)
    {
        $searchIndexTable = $this->scopeResolver->resolve($request->getIndex(), $request->getDimensions());
        $select = $this->getSelect()
            ->from(
                ['search_index' => $searchIndexTable],
                ['entity_id' => 'entity_id']
            )
            ->joinLeft(
                ['cea' => $this->resource->getTableName('catalog_eav_attribute')],
                'search_index.attribute_id = cea.attribute_id',
                []
            );

        if ($this->isNeedToAddFilters($request)) {
            $select
                ->joinLeft(
                    ['category_index' => $this->resource->getTableName('catalog_category_product_index')],
                    'search_index.entity_id = category_index.product_id',
                    []
                )
                ->joinLeft(
                    ['cpie' => $this->resource->getTableName('catalog_product_index_eav')],
                    'search_index.entity_id = cpie.entity_id AND search_index.attribute_id = cpie.attribute_id',
                    []
                );
        }

        $select = $this->processDimensions($request, $select);

        $isShowOutOfStock = $this->config->isSetFlag(
            'cataloginventory/options/show_out_of_stock',
            ScopeInterface::SCOPE_STORE
        );
        if ($isShowOutOfStock === false) {
            $select->joinLeft(
                ['stock_index' => $this->resource->getTableName('cataloginventory_stock_status')],
                'search_index.entity_id = stock_index.product_id'
                . $this->getReadConnection()->quoteInto(
                    ' AND stock_index.website_id = ?',
                    $this->storeManager->getWebsite()->getId()
                ),
                []
            );
            $select->where('stock_index.stock_status = ?', 1);
        }

        return $select;
    }

    /**
     * Add filtering by dimensions
     *
     * @param RequestInterface $request
     * @param Select $select
     * @return \Magento\Framework\DB\Select
     */
    private function processDimensions(RequestInterface $request, Select $select)
    {
        $dimensions = $this->prepareDimensions($request->getDimensions());

        $query = $this->conditionManager->combineQueries($dimensions, Select::SQL_OR);
        if (!empty($query)) {
            $select->where($this->conditionManager->wrapBrackets($query));
        }

        return $select;
    }

    /**
     * @param Dimension[] $dimensions
     * @return string[]
     */
    private function prepareDimensions(array $dimensions)
    {
        $preparedDimensions = [];
        foreach ($dimensions as $dimension) {
            if ('scope' === $dimension->getName()) {
                continue;
            }
            $preparedDimensions[] = $this->conditionManager->generateCondition(
                $dimension->getName(),
                '=',
                $dimension->getValue()
            );
        }

        return $preparedDimensions;
    }

    /**
     * Get read connection
     *
     * @return \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getReadConnection()
    {
        return $this->resource->getConnection(Resource::DEFAULT_READ_RESOURCE);
    }

    /**
     * Get empty Select
     *
     * @return Select
     */
    private function getSelect()
    {
        return $this->getReadConnection()->select();
    }

    /**
     * @param RequestInterface $request
     * @return bool
     */
    private function isNeedToAddFilters(RequestInterface $request)
    {
        return $this->hasFilters($request->getQuery());
    }

    /**
     * @param QueryInterface $query
     * @return bool
     */
    private function hasFilters(QueryInterface $query)
    {
        $hasFilters = false;
        switch ($query->getType()) {
            case RequestQueryInterface::TYPE_BOOL:
                /** @var \Magento\Framework\Search\Request\Query\Bool $query */
                foreach ($query->getMust() as $subQuery) {
                    $hasFilters |= $this->hasFilters($subQuery);
                }
                foreach ($query->getShould() as $subQuery) {
                    $hasFilters |= $this->hasFilters($subQuery);
                }
                foreach ($query->getMustNot() as $subQuery) {
                    $hasFilters |= $this->hasFilters($subQuery);
                }
                break;
            case RequestQueryInterface::TYPE_FILTER:
                $hasFilters |= true;
                break;
            default:
                $hasFilters |= false;
                break;
        }
        return $hasFilters;
    }
}
