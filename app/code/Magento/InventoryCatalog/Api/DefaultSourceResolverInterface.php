<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryCatalog\Api;

/**
 * Represents default source
 *
 * @api
 */
interface DefaultSourceResolverInterface
{
    /**
     * Get default source id
     *
     * @return int
     */
    public function getId(): int;
}
