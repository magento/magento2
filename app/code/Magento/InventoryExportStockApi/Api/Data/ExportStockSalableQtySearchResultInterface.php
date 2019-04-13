<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryExportStockApi\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for ExportStockSalableQtySearchResult
 * @api
 */
interface ExportStockSalableQtySearchResultInterface extends SearchResultsInterface
{
    /**
     * Get stock data array
     *
     * @return array
     */
    public function getItems();

    /**
     * Set stock data array
     *
     * @param array $items
     * @return $this
     */
    public function setItems(array $items);
}
