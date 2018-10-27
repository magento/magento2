<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\FilterMapper;

use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver;
use Magento\Customer\Model\Indexer\CustomerGroupDimensionProvider;
use Magento\Framework\App\Http\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Indexer\DimensionFactory;
use Magento\Framework\Search\Request\IndexScopeResolverInterface as TableResolver;
use Magento\Framework\Search\Request\Dimension;
use Magento\Catalog\Model\Indexer\Category\Product\AbstractAction;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Framework\Search\Request\IndexScopeResolverInterface;
use Magento\Store\Model\Indexer\WebsiteDimensionProvider;

/**
 * Strategy which processes exclusions from general rules
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ExclusionStrategy implements FilterStrategyInterface
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var AliasResolver
     */
    private $aliasResolver;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * List of fields that can be processed by exclusion strategy
     * @var array
     */
    private $validFields = ['price', 'category_ids'];

    /**
     * @var TableResolver
     */
    private $tableResolver;

    /**
     * @var IndexScopeResolverInterface
     */
    private $priceTableResolver;

    /**
     * @var DimensionFactory
     */
    private $dimensionFactory;

    /**
     * @var Context
     */
    private $httpContext;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param AliasResolver $aliasResolver
     * @param TableResolver|null $tableResolver
     * @param DimensionFactory $dimensionFactory
     * @param IndexScopeResolverInterface $priceTableResolver
     * @param Context $httpContext
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        AliasResolver $aliasResolver,
        TableResolver $tableResolver = null,
        DimensionFactory $dimensionFactory = null,
        IndexScopeResolverInterface $priceTableResolver = null,
        Context $httpContext = null
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->storeManager = $storeManager;
        $this->aliasResolver = $aliasResolver;
        $this->tableResolver = $tableResolver ?: ObjectManager::getInstance()->get(TableResolver::class);
        $this->dimensionFactory = $dimensionFactory ?: ObjectManager::getInstance()->get(DimensionFactory::class);
        $this->priceTableResolver = $priceTableResolver ?: ObjectManager::getInstance()->get(
            IndexScopeResolverInterface::class
        );
        $this->httpContext = $httpContext ?: ObjectManager::getInstance()->get(Context::class);
    }

    /**
     * {@inheritDoc}
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
     */
    private function applyPriceFilter(
        \Magento\Framework\Search\Request\FilterInterface $filter,
        \Magento\Framework\DB\Select $select
    ) {
        $alias = $this->aliasResolver->getAlias($filter);
        $websiteId = $this->storeManager->getWebsite()->getId();
        $tableName = $this->priceTableResolver->resolve(
            'catalog_product_index_price',
            [
                $this->dimensionFactory->create(WebsiteDimensionProvider::DIMENSION_NAME, (string)$websiteId),
                $this->dimensionFactory->create(
                    CustomerGroupDimensionProvider::DIMENSION_NAME,
                    (string)$this->httpContext->getValue(CustomerContext::CONTEXT_GROUP)
                )
            ]
        );
        $mainTableAlias = $this->extractTableAliasFromSelect($select);

        $select->joinInner(
            [
                $alias => $tableName
            ],
            $this->resourceConnection->getConnection()->quoteInto(
                sprintf('%s.entity_id = price_index.entity_id AND price_index.website_id = ?', $mainTableAlias),
                $websiteId
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
     */
    private function applyCategoryFilter(
        \Magento\Framework\Search\Request\FilterInterface $filter,
        \Magento\Framework\DB\Select $select
    ) {
        $alias = $this->aliasResolver->getAlias($filter);

        $catalogCategoryProductDimension = new Dimension(
            \Magento\Store\Model\Store::ENTITY,
            $this->storeManager->getStore()->getId()
        );

        $tableName = $this->tableResolver->resolve(
            AbstractAction::MAIN_INDEX_TABLE,
            [
                $catalogCategoryProductDimension
            ]
        );
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
