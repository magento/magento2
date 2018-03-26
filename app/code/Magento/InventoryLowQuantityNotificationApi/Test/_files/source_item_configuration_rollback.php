<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\TestFramework\Helper\Bootstrap;
use Magento\InventoryLowQuantityNotificationApi\Api\DeleteSourceItemConfigurationInterface;

/** @var DeleteSourceItemConfigurationInterface $deleteSourceItemConfiguration */
$deleteSourceItemConfiguration = Bootstrap::getObjectManager()->get(DeleteSourceItemConfigurationInterface::class);
$deleteSourceItemConfiguration->execute('eu-1', 'SKU-1');
$deleteSourceItemConfiguration->execute('eu-disabled', 'SKU-1');
$deleteSourceItemConfiguration->execute('eu-2', 'SKU-3');
