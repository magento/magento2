<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryCatalog\Api;

use Magento\InventoryApi\Api\Data\SourceInterface;

/**
 * Represents default source
 *
 * @api
 */
interface DefaultSourceRepositoryInterface
{
    const DEFAULT_SOURCE= 1;

    /**
     * Get default source
     *
     * @return SourceInterface
     */
    public function get(): SourceInterface;
}