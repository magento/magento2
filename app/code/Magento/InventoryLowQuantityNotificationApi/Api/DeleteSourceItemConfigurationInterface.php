<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotificationApi\Api;

/**
 * Delete the source item configuration
 *
 * @api
 */
interface DeleteSourceItemConfigurationInterface
{
    /**
     * @param string $sourceCode
     * @param string $sku
     * @return void
     */
    public function execute(string $sourceCode, string $sku);
}
