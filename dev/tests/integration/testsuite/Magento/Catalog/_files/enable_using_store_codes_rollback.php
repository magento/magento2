<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\App\Config\Value;

/** @var \Magento\Framework\ObjectManagerInterface $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var Value $value */
$value = $objectManager->create(Value::class);
try {
    $value->load('web/url/use_store', 'path');
    $value->delete();
} catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
    // do nothing
}

/** @var ReinitableConfigInterface $reinitableConfig */
$reinitableConfig = $objectManager->get(ReinitableConfigInterface::class);
$reinitableConfig->reinit();
