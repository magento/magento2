<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogSearch\Model\Search\FilterMapper;

use Magento\CatalogSearch\Model\Adapter\Mysql\Filter\AliasResolver;

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
                [
                    $alias => $tableName
                ],
                $this->resourceConnection->getConnection()->quoteInto(
                    'search_index.entity_id = price_index.entity_id AND price_index.website_id = ?',
                    $this->storeManager->getWebsite()->getId()
                ),
                []
            );
            $isApplied = true;
        } elseif ('category_ids' === $field) {
            $alias = $this->aliasResolver->getAlias($filter);
            $tableName = $this->resourceConnection->getTableName('catalog_category_product_index');
            $select->joinInner(
                [
                    $alias => $tableName
                ],
                $this->resourceConnection->getConnection()->quoteInto(
                    'search_index.entity_id = category_ids_index.product_id AND category_ids_index.store_id = ?',
                    $this->storeManager->getStore()->getId()
                ),
                []
            );
            $isApplied = true;
        }
        return $isApplied;
    }
}
