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
     * ExportStockIndexData constructor
     *
     * @param StockIndexDumpProcessor $stockIndexDumpProcessor
     * @param GetWebsiteIdByWebsiteCode $getWebsiteIdByWebsiteCode
     */
    public function __construct(
        StockIndexDumpProcessor $stockIndexDumpProcessor,
        GetWebsiteIdByWebsiteCode $getWebsiteIdByWebsiteCode
    ) {
        $this->stockIndexDumpProcessor = $stockIndexDumpProcessor;
        $this->getWebsiteIdByWebsiteCode = $getWebsiteIdByWebsiteCode;
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

        return $this->stockIndexDumpProcessor->execute($websiteId);
    }
}
