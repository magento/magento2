<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogSearch\Plugin\Model\Search\FilterMapper\TermDropdownStrategy;

use Magento\CatalogSearch\Model\Search\FilterMapper\TermDropdownStrategy\ApplyStockConditionToSelect
    as LegacyStockConditionApplier;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\InventoryCatalog\Model\GetStockIdForCurrentWebsite;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;

class ApplyStockConditionTo
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var GetStockIdForCurrentWebsite
     */
    private $getStockIdForCurrentWebsite;

    /**
     * @var StockIndexTableNameResolverInterface
     */
    private $stockIndexTableNameResolver;

    /**
     * @param ResourceConnection $resourceConnection
     * @param GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite
     * @param StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        GetStockIdForCurrentWebsite $getStockIdForCurrentWebsite,
        StockIndexTableNameResolverInterface $stockIndexTableNameResolver
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->getStockIdForCurrentWebsite = $getStockIdForCurrentWebsite;
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
    }

    /**
     * @param LegacyStockConditionApplier $stockConditionApplier
     * @param callable $proceed
     * @param string $alias
     * @param string $stockAlias
     * @param Select $select
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        LegacyStockConditionApplier $stockConditionApplier,
        callable $proceed,
        string $alias,
        string $stockAlias,
        Select $select
    ) {
        $select->joinInner(
            ['product' => $this->resourceConnection->getTableName('catalog_product_entity')],
            sprintf('product.entity_id = %s.source_id', $alias),
            []
        );
        $stockId = $this->getStockIdForCurrentWebsite->execute();
        $tableName = $this->stockIndexTableNameResolver->execute($stockId);

        $select->joinInner(
            [$stockAlias => $tableName],
            sprintf('product.sku = %1$s.sku', $stockAlias),
            []
        );
    }
}
