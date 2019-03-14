<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurationApi\Model;

/**
 * Is allowed management of source items for specific product sku
 *
 * @api
 */
interface IsSourceItemManagementAllowedForSkuInterface
{
    /**
     * Return whether Source Item management allowed for given SKU
     *
     * @param string $sku
     * @return bool
     */
    public function execute(string $sku): bool;
}
