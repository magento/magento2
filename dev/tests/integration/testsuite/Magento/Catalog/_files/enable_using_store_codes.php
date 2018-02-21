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
$value->setScope('default')
    ->setScopeId(0)
    ->setPath('web/url/use_store')
    ->setValue(1)
    ->save();

/** @var ReinitableConfigInterface $reinitableConfig */
$reinitableConfig = $objectManager->get(ReinitableConfigInterface::class);
$reinitableConfig->reinit();
