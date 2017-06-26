<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Search\Adapter\Mysql\ConditionManager;
use Magento\Framework\Search\Adapter\Mysql\IndexBuilderInterface;
use Magento\Framework\Search\Request\Dimension;
use Magento\Framework\Search\RequestInterface;
use Magento\Framework\Indexer\ScopeResolver\IndexScopeResolver;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\Stock;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\App\ObjectManager;
use Magento\CatalogSearch\Model\Search\QueryChecker\FullTextSearchCheck;

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
     * @deprecated
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
     * @var TableMapper
     */
    private $tableMapper;

    /**
     * @var ScopeResolverInterface
     */
    private $dimensionScopeResolver;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var FullTextSearchCheck
     */
    private $fullTextSearchCheck;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param ScopeConfigInterface $config
     * @param StoreManagerInterface $storeManager
     * @param ConditionManager $conditionManager
     * @param IndexScopeResolver $scopeResolver
     * @param TableMapper $tableMapper
     * @param ScopeResolverInterface $dimensionScopeResolver
     * @param FullTextSearchCheck $fullTextSearchCheck
     */
    public function __construct(
        ResourceConnection $resource,
        ScopeConfigInterface $config,
        StoreManagerInterface $storeManager,
        ConditionManager $conditionManager,
        IndexScopeResolver $scopeResolver,
        TableMapper $tableMapper,
        ScopeResolverInterface $dimensionScopeResolver,
        FullTextSearchCheck $fullTextSearchCheck = null
    ) {
        $this->resource = $resource;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->conditionManager = $conditionManager;
        $this->scopeResolver = $scopeResolver;
        $this->tableMapper = $tableMapper;
        $this->dimensionScopeResolver = $dimensionScopeResolver;
        $this->fullTextSearchCheck = $fullTextSearchCheck ?: ObjectManager::getInstance()
            ->get(FullTextSearchCheck::class);
    }

    /**
     * Build index query
     *
     * @param RequestInterface $request
     * @return Select
     * @throws \LogicException
     * @throws \DomainException
     * @throws \InvalidArgumentException
     */
    public function build(RequestInterface $request)
    {
        $searchIndexTable = $this->scopeResolver->resolve($request->getIndex(), $request->getDimensions());
        $select = $this->resource->getConnection()->select()
            ->from(
                ['search_index' => $searchIndexTable],
                ['entity_id' => 'entity_id']
            );

        if ($this->fullTextSearchCheck->isRequiredForQuery($request->getQuery())) {
            $select->joinLeft(
                ['cea' => $this->resource->getTableName('catalog_eav_attribute')],
                'search_index.attribute_id = cea.attribute_id',
                []
            );
        }

        $select = $this->tableMapper->addTables($select, $request);

        $select = $this->processDimensions($request, $select);

        $isShowOutOfStock = $this->config->isSetFlag(
            'cataloginventory/options/show_out_of_stock',
            ScopeInterface::SCOPE_STORE
        );
        if ($isShowOutOfStock === false) {
            $select->joinInner(
                ['stock_index' => $this->resource->getTableName('cataloginventory_stock_status')],
                'search_index.entity_id = stock_index.product_id'
                . $this->resource->getConnection()->quoteInto(
                    ' AND stock_index.website_id = ?',
                    $this->getStockConfiguration()->getDefaultScopeId()
                ),
                []
            );
            $select->where('stock_index.stock_status = ?', Stock::STOCK_IN_STOCK);
        }

        return $select;
    }

    /**
     * @return StockConfigurationInterface
     *
     * @deprecated
     */
    private function getStockConfiguration()
    {
        if ($this->stockConfiguration === null) {
            $this->stockConfiguration = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\CatalogInventory\Api\StockConfigurationInterface::class);
        }
        return $this->stockConfiguration;
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
                $this->dimensionScopeResolver->getScope($dimension->getValue())->getId()
            );
        }

        return $preparedDimensions;
    }
}
