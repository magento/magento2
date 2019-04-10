<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStock\Model;

use Exception;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\InventoryExportStockApi\Api\Data\ExportStockDataSearchResultInterface;
use Magento\InventoryExportStockApi\Api\ExportStockDataInterface;

/**
 * Class ExportStockData
 */
class ExportStockData implements ExportStockDataInterface
{
    /**
     * @var GetExportStockData
     */
    private $getExportStockData;

    /**
     * ExportStockData constructor.
     * @param GetExportStockData $getExportStockData
     */
    public function __construct(
        GetExportStockData $getExportStockData
    ) {
        $this->getExportStockData = $getExportStockData;
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function execute(
        SearchCriteriaInterface $searchCriteria,
        int $stockId = null,
        int $qtyForNotManageStock = 1
    ): ExportStockDataSearchResultInterface {
        return $this->getExportStockData->execute($searchCriteria, $stockId, $qtyForNotManageStock);
    }
}
