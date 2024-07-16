<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedImportExport\Plugin\CatalogImportExport\Model\StockItemImporter;

use Magento\CatalogImportExport\Model\StockItemImporterInterface;
use Magento\GroupedProduct\Model\Inventory\ChangeParentStockStatus;

/**
 * Update grouped product stock status during import plugin.
 */
class UpdateGroupedProductStockStatusPlugin
{
    /**
     * @var ChangeParentStockStatus
     */
    private $changeParentStockStatus;

    /**
     * @param ChangeParentStockStatus $changeParentStockStatus
     */
    public function __construct(ChangeParentStockStatus $changeParentStockStatus)
    {
        $this->changeParentStockStatus = $changeParentStockStatus;
    }

    /**
     * Update grouped product stock status during import.
     *
     * @param StockItemImporterInterface $subject
     * @param null $result
     * @param array $stockData
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterImport(
        StockItemImporterInterface $subject,
        $result,
        array $stockData
    ) {
        $productIds = array_column($stockData, 'product_id');
        foreach ($productIds as $productId) {
            $this->changeParentStockStatus->execute((int)$productId);
        }
    }
}
