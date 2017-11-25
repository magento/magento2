<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurationApi\Api;

/**
 * Get the source configuration for a product.
 *
 * Used fully qualified namespaces in annotations for proper work of WebApi request parser
 *
 * @api
 */
interface DeleteSourceItemConfigurationInterface
{
    /**
     * Delete the SourceItem Configuration data
     *
     * @param int $sourceId
     * @param string $sku
     * @return void
     */
    public function execute(int $sourceId, string $sku);
}
