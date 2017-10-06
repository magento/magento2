<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryCatalog\Model;

use Magento\InventoryCatalog\Api\DefaultStockProviderInterface;

/**
 * Service returns Default Stock Id
 */
class DefaultStockProvider implements DefaultStockProviderInterface
{
    /**
     * @inheritdoc
     */
    public function getId(): int
    {
        return 1;
    }
}
