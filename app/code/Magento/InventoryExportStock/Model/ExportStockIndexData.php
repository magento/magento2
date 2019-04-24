<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStock\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryExportStock\Model\ResourceModel\StockIndexDumpProcessor;
use Magento\InventoryExportStockApi\Api\Data\ProductStockIndexDataInterface;
use Magento\InventoryExportStockApi\Api\Data\ProductStockIndexDataInterfaceFactory;
use Magento\InventoryExportStockApi\Api\ExportStockIndexDataInterface;
use Magento\InventorySales\Model\ResourceModel\GetWebsiteIdByWebsiteCode;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;

/**
 * Class ExportStockIndexData provides stock index export
 */
class ExportStockIndexData implements ExportStockIndexDataInterface
{
    /**
     * @var StockIndexDumpProcessor
     */
    private $stockIndexDumpProcessor;

    /**
     * @var GetWebsiteIdByWebsiteCode
     */
    private $getWebsiteIdByWebsiteCode;

    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * @var ProductStockIndexDataMapper
     */
    private $productStockIndexDataMapper;

    /**
     * ExportStockIndexData constructor
     *
     * @param StockIndexDumpProcessor $stockIndexDumpProcessor
     * @param GetWebsiteIdByWebsiteCode $getWebsiteIdByWebsiteCode
     * @param StockResolverInterface $stockResolver
     * @param ProductStockIndexDataMapper $productStockIndexDataMapper
     */
    public function __construct(
        StockIndexDumpProcessor $stockIndexDumpProcessor,
        GetWebsiteIdByWebsiteCode $getWebsiteIdByWebsiteCode,
        StockResolverInterface $stockResolver,
        ProductStockIndexDataMapper $productStockIndexDataMapper
    ) {
        $this->stockIndexDumpProcessor = $stockIndexDumpProcessor;
        $this->getWebsiteIdByWebsiteCode = $getWebsiteIdByWebsiteCode;
        $this->stockResolver = $stockResolver;
        $this->productStockIndexDataMapper = $productStockIndexDataMapper;
    }

    /**
     * Provides stock index export from inventory_stock_% table
     *
     * @param string $websiteCode
     * @return ProductStockIndexDataInterface[]
     * @throws LocalizedException
     */
    public function execute(string $websiteCode): array
    {
        $websiteId = $this->getWebsiteIdByWebsiteCode->execute($websiteCode);
        $stockId = $this->stockResolver
            ->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode)->getStockId();
        $items = $this->stockIndexDumpProcessor->execute($websiteId, $stockId);
        $productsData = [];
        foreach ($items as $item) {
            $productsData[] = $this->productStockIndexDataMapper->execute($item);
        }

        return $productsData;
    }
}
