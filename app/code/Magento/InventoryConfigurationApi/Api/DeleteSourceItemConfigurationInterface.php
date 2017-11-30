<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurationApi\Api;

/**
 * Delete the source item configuration
 *
 * @api
 */
interface DeleteSourceItemConfigurationInterface
{
    /**
     * @param int $sourceId
     * @param string $sku
     * @return void
     */
    public function execute(int $sourceId, string $sku);
}
