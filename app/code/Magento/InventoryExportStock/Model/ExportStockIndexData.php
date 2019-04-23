<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStock\Model;

use Exception;
use Magento\Framework\Exception\LocalizedException;
use Magento\InventoryExportStock\Model\ResourceModel\StockIndexDumpProcessor;
use Magento\InventoryExportStockApi\Api\ExportStockIndexDataInterface;
use Magento\InventorySales\Model\ResourceModel\GetWebsiteIdByWebsiteCode;
use Psr\Log\LoggerInterface;

/**
 * Class ExportStockIndexData
 */
class ExportStockIndexData implements ExportStockIndexDataInterface
{
    /**
     * @var StockIndexDumpProcessor
     */
    private $stockIndexDumpProcessor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var GetWebsiteIdByWebsiteCode
     */
    private $getWebsiteIdByWebsiteCode;

    /**
     * ExportStockIndexData constructor
     *
     * @param StockIndexDumpProcessor $stockIndexDumpProcessor
     * @param LoggerInterface $logger
     * @param GetWebsiteIdByWebsiteCode $getWebsiteIdByWebsiteCode
     */
    public function __construct(
        StockIndexDumpProcessor $stockIndexDumpProcessor,
        LoggerInterface $logger,
        GetWebsiteIdByWebsiteCode $getWebsiteIdByWebsiteCode
    ) {
        $this->stockIndexDumpProcessor = $stockIndexDumpProcessor;
        $this->logger = $logger;
        $this->getWebsiteIdByWebsiteCode = $getWebsiteIdByWebsiteCode;
    }

    /**
     * Provides stock index export from inventory_stock_% table
     *
     * @param string $websiteCode
     * @return array
     * @throws LocalizedException
     */
    public function execute(
        string $websiteCode
    ): array {
        try {
            $websiteId = $this->getWebsiteIdByWebsiteCode->execute($websiteCode);
            $items = $this->stockIndexDumpProcessor->execute($websiteId);
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage(), $e->getTrace());
            throw new LocalizedException(__('Something went wrong. Export couldn\'t be executed, See log files for error details'));
        }

        return $items;
    }
}
