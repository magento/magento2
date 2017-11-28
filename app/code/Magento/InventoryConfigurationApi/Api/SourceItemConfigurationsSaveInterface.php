<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InventoryConfigurationApi\Api;

use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterface;

/**
 * Save the sources configurations for a the product.
 *
 * Used fully qualified namespaces in annotations for proper work of WebApi request parser
 *
 * @api
 */
interface SourceItemConfigurationsSaveInterface
{
    /**
     * Save the configuration of source Items.
     *
     * @param \Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterface[] $configuration
     * @return void
     */
    public function execute(array $configuration);
}