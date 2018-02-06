<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model;

/**
 * Is allowed manage source items for specific product type
 *
 * @api
 */
interface IsManageSourceItemsAllowedForProductTypeInterface
{
    /**
     * @param string $productType
     * @return bool
     */
    public function execute(string $productType): bool;
}
