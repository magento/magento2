<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryLowQuantityNotificationApi\Api;

/**
 * Save the source item configuration
 *
 * Used fully qualified namespaces in annotations for proper work of WebApi request parser
 *
 * @api
 */
interface SourceItemConfigurationsSaveInterface
{
    /**
     * @param \Magento\InventoryLowQuantityNotificationApi\Api\Data\SourceItemConfigurationInterface[]
     *      $sourceItemConfigurations
     * @return void
     */
    public function execute(array $sourceItemConfigurations): void;
}
