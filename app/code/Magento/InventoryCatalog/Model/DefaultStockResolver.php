<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryCatalog\Model;

use Magento\InventoryCatalog\Api\DefaultStockResolverInterface;

/**
 * Class DefaultStockRepository
 */
class DefaultStockResolver implements DefaultStockResolverInterface
{
    /**
     * Get default stock
     *
     * @return int
     */
    public function getId(): int
    {
        return 1;
    }

}
