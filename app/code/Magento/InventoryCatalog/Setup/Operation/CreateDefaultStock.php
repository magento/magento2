<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Setup\Operation;

use Magento\Framework\App\ResourceConnection;
use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;

/**
 * Create default stock during installation
 */
class CreateDefaultStock
{
    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param ResourceConnection $resource
     */
    public function __construct(
        DefaultStockProviderInterface $defaultStockProvider,
        ResourceConnection $resource
    ) {
        $this->defaultStockProvider = $defaultStockProvider;
        $this->resource = $resource;
    }

    /**
     * Create default stock
     *
     * @return void
     */
    public function execute()
    {
        $connection = $this->resource->getConnection();
        $stockData = [
            StockInterface::STOCK_ID => $this->defaultStockProvider->getId(),
            StockInterface::NAME => 'Default Stock',
        ];
        $connection->insert($this->resource->getTableName('inventory_stock'), $stockData);
    }
}
