<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Model;

/**
 * Is allowed management of  source items for specific product type
 *
 * @api
 */
interface IsSourceItemsAllowedForProductTypeInterface
{
    /**
     * @param string $productType
     * @return bool
     */
    public function execute(string $productType): bool;
}
