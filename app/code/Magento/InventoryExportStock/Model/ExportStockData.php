<?php

namespace Magento\InventoryExportStock\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\InventoryExportStockApi\Api\Data\ExportStockDataSearchResultInterface;
use Magento\InventoryExportStockApi\Api\ExportStockDataInterface;

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
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(
        SearchCriteriaInterface $searchCriteria,
        int $stockId = null,
        int $qtyForNotManageStock = 1
    ): ExportStockDataSearchResultInterface {
        return $this->getExportStockData->execute($searchCriteria, $stockId, $qtyForNotManageStock);
    }
}
