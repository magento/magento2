<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalog\Model;

/**
 * Update Legacy catalocinventory_stock_status database data
 */
class UpdateLegacyStockStatusByPlainQuery
{
    /**
     * @var LegacyUpdateService
     */
    private $legacyUpdateService;

    public function __construct(
        LegacyUpdateService $legacyUpdateService
    ) {
        $this->legacyUpdateService = $legacyUpdateService;
    }

    /**
     * Execute Plain MySql query on catalaginventory_stock_status
     *
     * @param string $sku
     * @param float $quantity
     * @return void
     */
    public function execute(string $sku, float $quantity)
    {
        $this->legacyUpdateService->execute($sku, $quantity, LegacyUpdateService::TYPE_STOCK_STATUS);
    }
}
