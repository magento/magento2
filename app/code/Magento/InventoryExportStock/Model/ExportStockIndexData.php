<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStock\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryExportStock\Model\ResourceModel\StockIndexDumpProcessor;
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
     * ExportStockIndexData constructor
     *
     * @param StockIndexDumpProcessor $stockIndexDumpProcessor
     * @param GetWebsiteIdByWebsiteCode $getWebsiteIdByWebsiteCode
     * @param StockResolverInterface $stockResolver
     */
    public function __construct(
        StockIndexDumpProcessor $stockIndexDumpProcessor,
        GetWebsiteIdByWebsiteCode $getWebsiteIdByWebsiteCode,
        StockResolverInterface $stockResolver
    ) {
        $this->stockIndexDumpProcessor = $stockIndexDumpProcessor;
        $this->getWebsiteIdByWebsiteCode = $getWebsiteIdByWebsiteCode;
        $this->stockResolver = $stockResolver;
    }

    /**
     * Provides stock index export from inventory_stock_% table
     *
     * @param string $websiteCode
     * @return array
     * @throws LocalizedException
     */
    public function execute(string $websiteCode): array
    {
        $websiteId = $this->getWebsiteIdByWebsiteCode->execute($websiteCode);
        $stockId = $this->stockResolver
            ->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode)->getStockId();

        return $this->stockIndexDumpProcessor->execute($websiteId, $stockId);
    }
}
