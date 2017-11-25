<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryConfiguration\Model;

/**
 * Interface GetSourceItemConfigurationInterface
 */
interface GetSourceItemConfigurationInterface
{
    /**
     * Get the source item configuration.
     *
     * @param int $sourceId
     * @param string $sku
     *
     * @return array|null
     */
    public function execute(int $sourceId, string $sku);
}
