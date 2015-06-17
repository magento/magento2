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
use Magento\Framework\Search\Adapter\Mysql\ScoreBuilder;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Search\RequestInterface;
use Magento\Search\Model\IndexScopeResolver;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Build base Query for Index
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
        $this->scopeResolver = $scopeResolver;
        $this->conditionManager = $conditionManager;
    }

    /**
     * Build index query
     *
     * @param RequestInterface $request
     * @return Select
     */
    public function build(RequestInterface $request)
    {
        $select = $this->getSelect()
            ->from(
                ['search_index' => $this->getScopeTableName($request)],
                ['entity_id' => 'product_id']
            )
            ->joinLeft(
                ['category_index' => $this->resource->getTableName('catalog_category_product_index')],
                'search_index.product_id = category_index.product_id',
                []
            )
            ->joinLeft(
                ['cea' => $this->resource->getTableName('catalog_eav_attribute')],
                'search_index.attribute_id = cea.attribute_id',
                [ScoreBuilder::WEIGHT_FIELD]
            )
            ->joinLeft(
                ['cpie' => $this->resource->getTableName('catalog_product_index_eav')],
                'search_index.product_id = cpie.entity_id AND search_index.attribute_id = cpie.attribute_id',
                []
            );

        $select = $this->processDimensions($request, $select);

        $isShowOutOfStock = $this->config->isSetFlag(
            'cataloginventory/options/show_out_of_stock',
            ScopeInterface::SCOPE_STORE
        );
        if ($isShowOutOfStock === false) {
            $select->joinLeft(
                ['stock_index' => $this->resource->getTableName('cataloginventory_stock_status')],
                'search_index.product_id = stock_index.product_id'
                . $this->getReadConnection()->quoteInto(
                    ' AND stock_index.website_id = ?',
                    $this->storeManager->getWebsite()->getId()
                ),
                []
            )
                ->where('stock_index.stock_status = ?', 1);
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
     * @return array
     */
    private function getScopeTableName(RequestInterface $request)
    {
        $storeId = null;
        /** @var \Magento\Framework\Search\Request\Dimension $dimension */
        foreach ($request->getDimensions() as $dimension) {
            if ('scope' === $dimension->getName()) {
                $storeId = $dimension->getValue();
                break;
            }
        }
        $this->scopeResolver->resolve($request->getIndex(), $storeId);
        $tableName = $this->resource->getTableName([$request->getIndex(), 'index_' . $storeId]);
        return $tableName;
    }
}
