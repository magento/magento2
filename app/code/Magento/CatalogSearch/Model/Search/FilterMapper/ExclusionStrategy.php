<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\FilterMapper;

use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver;
use Magento\Framework\App\ObjectManager;
use Magento\Indexer\Model\ResourceModel\FrontendResource;

/**
 * Strategy which processes exclusions from general rules
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
     * @var FrontendResource
     */
    private $indexerFrontendResource;

    /**
     * @var \Magento\Indexer\Model\ResourceModel\FrontendResource|null
     */
    private $categoryProductIndexerFrontend;

    /**
     * List of fields that can be processed by exclusion strategy
     * @var array
     */
    private $validFields = ['price', 'category_ids'];

    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param AliasResolver $aliasResolver
     * @param FrontendResource $indexerFrontendResource
     * @param FrontendResource $categoryProductIndexerFrontend
     * @SuppressWarnings(Magento.TypeDuplication)
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        AliasResolver $aliasResolver,
        FrontendResource $indexerFrontendResource = null,
        FrontendResource $categoryProductIndexerFrontend = null
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->storeManager = $storeManager;
        $this->aliasResolver = $aliasResolver;
        $this->indexerFrontendResource = $indexerFrontendResource ?: ObjectManager::getInstance()->get(
            \Magento\Catalog\Model\ResourceModel\Product\Indexer\Price\FrontendResource::class
        );
        $this->categoryProductIndexerFrontend = $categoryProductIndexerFrontend ?: ObjectManager::getInstance()->get(
            \Magento\Catalog\Model\ResourceModel\Product\Indexer\Category\Product\FrontendResource::class
        );
    }

    /**
     * {@inheritDoc}
     */
    public function apply(
        \Magento\Framework\Search\Request\FilterInterface $filter,
        \Magento\Framework\DB\Select $select
    ) {
        if (!in_array($filter->getField(), $this->validFields)) {
            return false;
        }

        if ($filter->getField() === 'price') {
            return $this->applyPriceFilter($filter, $select);
        } elseif ($filter->getField() === 'category_ids') {
            return $this->applyCategoryFilter($filter, $select);
        }
    }

    /**
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
        $tableName = $this->indexerFrontendResource->getMainTable();
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
        $tableName = $this->categoryProductIndexerFrontend->getMainTable();
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
