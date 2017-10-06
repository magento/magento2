<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryCatalog\Model;

use Magento\InventoryCatalog\Api\DefaultSourceResolverInterface;

/**
 * Class DefaultSourceResolver
 */
class DefaultSourceResolver implements DefaultSourceResolverInterface
{
    /**
     * Get default source id
     *
     * @return int
     */
    public function getId(): int
    {
        return 1;
    }

}
