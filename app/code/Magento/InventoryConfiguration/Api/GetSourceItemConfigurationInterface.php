<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryConfiguration\Api;

use Magento\InventoryConfiguration\Api\Data\SourceItemConfigurationInterface;

/**
 * Represents amount of product on physical storage
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
     * @param string $sourceId
     * @param string $sku
     * @return SourceItemConfigurationInterface
     */
    public function getSourceItemConfiguration(string $sourceId, string $sku): SourceItemConfigurationInterface;
}