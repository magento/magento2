<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurationApi\Api;

use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterface;

/**
 * Get the source configuration for a product.
 *
 * Used fully qualified namespaces in annotations for proper work of WebApi request parser
 *
 * @api
 */
interface GetSourceItemConfigurationInterface
{
    /**
     * Get the source item configuration.
     *
     * @param int $sourceId
     * @param string $sku
     * @return \Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterface
     */
    public function get(int $sourceId, string $sku): SourceItemConfigurationInterface;
}