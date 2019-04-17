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
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
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
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * ExportStockIndexData constructor
     *
     * @param StockIndexDumpProcessor $stockIndexDumpProcessor
     * @param StockResolverInterface $stockResolver
     * @param LoggerInterface $logger
     */
    public function __construct(
        StockIndexDumpProcessor $stockIndexDumpProcessor,
        StockResolverInterface $stockResolver,
        LoggerInterface $logger
    ) {
        $this->stockIndexDumpProcessor = $stockIndexDumpProcessor;
        $this->stockResolver = $stockResolver;
        $this->logger = $logger;
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
            $stockId = $this->stockResolver
                ->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode)->getStockId();
            $items = $this->stockIndexDumpProcessor->execute($stockId);
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage(), $e->getTrace());
            throw new LocalizedException(_('Something went wrong. Export couldn\'t be executed, See log files for error details'));
        }

        return $items;
    }
}
