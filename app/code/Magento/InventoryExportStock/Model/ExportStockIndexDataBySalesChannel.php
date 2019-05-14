<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStock\Model;

use Magento\InventoryExportStock\Model\ResourceModel\StockIndexDumpProcessor;
use Magento\InventoryExportStockApi\Api\ExportStockIndexDataBySalesChannelInterface;
use Magento\InventorySales\Model\ResourceModel\GetWebsiteIdByWebsiteCode;
use Magento\InventorySalesApi\Api\GetStockBySalesChannelInterface;

class ExportStockIndexDataBySalesChannel implements ExportStockIndexDataBySalesChannelInterface
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
     * @var ProductStockIndexDataMapper
     */
    private $productStockIndexDataMapper;

    /**
     * @var GetStockBySalesChannelInterface
     */
    private $getStockBySalesChannel;

    /**
     * @param StockIndexDumpProcessor $stockIndexDumpProcessor
     * @param GetWebsiteIdByWebsiteCode $getWebsiteIdByWebsiteCode
     * @param ProductStockIndexDataMapper $productStockIndexDataMapper
     * @param GetStockBySalesChannelInterface $getStockBySalesChannel
     */
    public function __construct(
        StockIndexDumpProcessor $stockIndexDumpProcessor,
        GetWebsiteIdByWebsiteCode $getWebsiteIdByWebsiteCode,
        ProductStockIndexDataMapper $productStockIndexDataMapper,
        GetStockBySalesChannelInterface $getStockBySalesChannel
    ) {
        $this->stockIndexDumpProcessor = $stockIndexDumpProcessor;
        $this->getWebsiteIdByWebsiteCode = $getWebsiteIdByWebsiteCode;
        $this->productStockIndexDataMapper = $productStockIndexDataMapper;
        $this->getStockBySalesChannel = $getStockBySalesChannel;
    }

    /**
     * @inheritDoc
     */
    public function execute(\Magento\InventorySalesApi\Api\Data\SalesChannelInterface $salesChannel): array
    {
        $stock = $this->getStockBySalesChannel->execute($salesChannel);
        $websiteId = $this->getWebsiteIdByWebsiteCode->execute($salesChannel->getCode());
        $items = $this->stockIndexDumpProcessor->execute($websiteId, $stock->getStockId());
        $productsData = [];
        foreach ($items as $item) {
            $productsData[] = $this->productStockIndexDataMapper->execute($item);
        }

        return $productsData;
    }
}
