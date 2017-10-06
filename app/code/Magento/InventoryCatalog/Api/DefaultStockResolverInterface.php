<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryCatalog\Api;

/**
 * Represents default stock
 *
 * @api
 */
interface DefaultStockResolverInterface
{

    /**
     * Get default stock
     *
     * @return int
     */
    public function getId(): int;
}
