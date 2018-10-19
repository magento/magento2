<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySalesApi\Model;

/**
 * Delete link between Stock and Sales Channel (Service Provider Interface - SPI)
 *
 * @api
 */
interface DeleteSalesChannelToStockLinkInterface
{
    /**
     * @param string $type
     * @param string $code
     * @return void
     */
    public function execute(string $type, string $code): void;
}
