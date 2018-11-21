<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryApi\Api;

/**
 * Sugar service for find SourceItems by SKU
 *
 * @api
 */
interface GetSourceItemsBySkuInterface
{
    /**
     * @param string $sku
     * @return \Magento\InventoryApi\Api\Data\SourceItemInterface[]
     */
    public function execute(string $sku): array;
}
