<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\InventoryCatalog\Api\DefaultSourceProviderInterface;
use Magento\Catalog\Model\ProductIdLocatorInterface;

class LegacyUpdateService
{
    const TYPE_STOCK_ITEM = 'stock_item';
    const TYPE_STOCK_STATUS = 'stock_status';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var ProductIdLocatorInterface
     */
    private $productIdLocator;

    /**
     * @var array
     */
    private $tableName;

    /**
     * @var array
     */
    private $params;

    /**
     * @param ResourceConnection $resourceConnection
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param ProductIdLocatorInterface $productIdLocator
     * @param array $tableName
     * @param array $params
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        DefaultSourceProviderInterface $defaultSourceProvider,
        ProductIdLocatorInterface $productIdLocator,
        array $tableName,
        array $params
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->productIdLocator = $productIdLocator;
        $this->tableName = $tableName;
        $this->params = $params;
    }

    /**
     * Execute Plain MySql query
     *
     * @param string $sku
     * @param float $quantity
     * @param string $type
     * @return void
     */
    public function execute(string $sku, float $quantity, string $type)
    {
        $tableName = $this->tableName[$type];
        $params = $this->params[$type];

        $productId = array_keys($this->productIdLocator->retrieveProductIdsBySkus([$sku])[$sku]);
        $productId = array_pop($productId);

        $connection = $this->resourceConnection->getConnection();
        $connection->update(
            $this->resourceConnection->getTableName($tableName),
            [
                $params['qty_key'] => new \Zend_Db_Expr(sprintf('%s + %s', StockStatusInterface::QTY, $quantity))
            ],
            [
                $params['stock_id_key'] . ' = ?' => $this->defaultSourceProvider->getId(),
                $params['product_id_key'] . ' = ?' => $productId,
                'website_id = ?' => 0,
            ]
        );
    }
}
