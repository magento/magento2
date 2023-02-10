<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\BundleImportExport\Plugin\Import\Product;

use Magento\CatalogImportExport\Model\StockItemImporterInterface;
use Magento\Bundle\Model\Inventory\ChangeParentStockStatus;

/**
 * Update bundle products stock item status based on children products stock status after import
 */
class UpdateBundleProductsStockItemStatusPlugin
{
    /**
     * @var ChangeParentStockStatus
     */
    private $changeParentStockStatus;

    /**
     * @param ChangeParentStockStatus $changeParentStockStatus
     */
    public function __construct(
        ChangeParentStockStatus $changeParentStockStatus
    ) {
        $this->changeParentStockStatus = $changeParentStockStatus;
    }

    /**
     * Update bundle products stock item status based on children products stock status after import
     *
     * @param StockItemImporterInterface $subject
     * @param mixed $result
     * @param array $stockData
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterImport(
        StockItemImporterInterface $subject,
        $result,
        array $stockData
    ): void {
        if ($stockData) {
            $this->changeParentStockStatus->execute(array_column($stockData, 'product_id'));
        }
    }
}
