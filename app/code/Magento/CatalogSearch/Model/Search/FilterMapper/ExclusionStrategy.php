<?php
/**
 * Copyright © 2013-2018 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\FilterMapper;

use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver;

/**
 * Strategy which processes exclusions from general rules.
 */
class ExclusionStrategy implements FilterStrategyInterface
{
    /**
     * Resource connection.
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
     private $resourceConnection;

    /**
     * Resolving table alias for Search Request filter.
     *
     * @var AliasResolver
     */
    private $aliasResolver;

    /**
     * Store manager interface.
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param AliasResolver $aliasResolver
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
     */
    public function apply(
        \Magento\Framework\Search\Request\FilterInterface $filter,
        \Magento\Framework\DB\Select $select
    ) {
        $isApplied = false;
        $field = $filter->getField();
        if ('price' === $field) {
            $alias = $this->aliasResolver->getAlias($filter);
            $tableName = $this->resourceConnection->getTableName('catalog_product_index_price');
            $select->joinInner(
                [$alias => $tableName],
                $this->resourceConnection->getConnection()->quoteInto(
                    'search_index.entity_id = price_index.entity_id AND price_index.website_id = ?',
                    $this->storeManager->getWebsite()->getId()
                ),
                []
            );
            $isApplied = true;
        } elseif ('category_ids' === $field || $field === 'visibility') {
            $alias = $this->aliasResolver->getAlias($filter);
            if (!array_key_exists($alias, $select->getPart('from'))) {
                $tableName = $this->resourceConnection->getTableName(
                    'catalog_category_product_index'
                );
                $select->joinInner(
                    [$alias => $tableName],
                    "search_index.entity_id = $alias.product_id",
                    []
                );
            }
            $isApplied = true;
        }

        return $isApplied;
    }
}
