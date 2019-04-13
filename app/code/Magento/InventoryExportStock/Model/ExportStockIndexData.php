<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStock\Model;

use Magento\InventoryExportStock\Model\ResourceModel\GetStockIndexDump;
use Magento\InventoryExportStockApi\Api\ExportStockIndexDataInterface;
use Zend_Db_Select_Exception;

/**
 * Class ExportStockIndexData
 */
class ExportStockIndexData implements ExportStockIndexDataInterface
{
    /**
     * @var GetStockIndexDump
     */
    private $getStockIndexDump;

    /**
     * ExportStockIndexData constructor
     *
     * @param GetStockIndexDump $getStockIndexDump
     */
    public function __construct(
        GetStockIndexDump $getStockIndexDump
    ) {
        $this->getStockIndexDump = $getStockIndexDump;
    }

    /**
     * Provides stock index export from inventory_stock_% table
     *
     * @param int $stockId
     * @return array
     * @throws Zend_Db_Select_Exception
     */
    public function execute(
        int $stockId
    ): array {
        return $this->getStockIndexDump->execute($stockId);
    }
}
