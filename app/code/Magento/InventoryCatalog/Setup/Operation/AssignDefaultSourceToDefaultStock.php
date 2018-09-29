<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Setup\Operation;

use Magento\Framework\App\ResourceConnection;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;

/**
 * Assign default source to default stock
 */
class AssignDefaultSourceToDefaultStock
{
    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param ResourceConnection $resource
     */
    public function __construct(
        DefaultStockProviderInterface $defaultStockProvider,
        DefaultSourceProviderInterface $defaultSourceProvider,
        ResourceConnection $resource
    ) {
        $this->defaultStockProvider = $defaultStockProvider;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->resource = $resource;
    }

    /**
     * Assign default source to stock
     *
     * @return void
     */
    public function execute()
    {
        $connection = $this->resource->getConnection();
        $stockSourceLinkData = [
            StockSourceLinkInterface::SOURCE_CODE => $this->defaultSourceProvider->getCode(),
            StockSourceLinkInterface::STOCK_ID => $this->defaultStockProvider->getId(),
            StockSourceLinkInterface::PRIORITY => 1,
        ];
        $connection->insert($this->resource->getTableName('inventory_source_stock_link'), $stockSourceLinkData);
    }
}
