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
interface SourceItemConfigurationSaveInterface
{
    /**
     * Save the source Item configuration.
     *
     * @param SourceItemConfigurationInterface[] $configuration
     * @return void
     */
    public function saveSourceItemConfiguration(array $configuration);
}