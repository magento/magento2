<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Setup\Patch\Schema;

use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;

/**
 * Creates MySQL View to use when Default Stock is used.
 */
class CreateLegacyStockStatusView implements SchemaPatchInterface
{
    /**
     * @var SchemaSetupInterface
     */
    private $schemaSetup;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @var StockIndexTableNameResolverInterface
     */
    private $stockIndexTableNameResolver;

    /**
     * @param SchemaSetupInterface $schemaSetup
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param StockIndexTableNameResolverInterface $stockIndexTableNameResolver
     */
    public function __construct(
        SchemaSetupInterface $schemaSetup,
        DefaultStockProviderInterface $defaultStockProvider,
        StockIndexTableNameResolverInterface $stockIndexTableNameResolver
    ) {
        $this->schemaSetup = $schemaSetup;
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->defaultStockProvider = $defaultStockProvider;
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function apply()
    {
        $this->schemaSetup->startSetup();
        $defaultStockId = $this->defaultStockProvider->getId();
        $viewToLegacyIndex = $this->stockIndexTableNameResolver->execute($defaultStockId);
        $legacyStockStatusTable = $this->schemaSetup->getTable('cataloginventory_stock_status');
        $productTable = $this->schemaSetup->getTable('catalog_product_entity');
        $sql = "CREATE
                SQL SECURITY INVOKER
                VIEW {$viewToLegacyIndex}
                  AS
                    SELECT
                    DISTINCT    
                      legacy_stock_status.product_id,
                      legacy_stock_status.website_id,
                      legacy_stock_status.stock_id,
                      legacy_stock_status.qty quantity,
                      legacy_stock_status.stock_status is_salable,
                      product.sku
                    FROM {$legacyStockStatusTable} legacy_stock_status
                      INNER JOIN {$productTable} product
                        ON legacy_stock_status.product_id = product.entity_id;";
        $this->schemaSetup->getConnection()->query($sql);
        $this->schemaSetup->endSetup();

        return $this;
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
