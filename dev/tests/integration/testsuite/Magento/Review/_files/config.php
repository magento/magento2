<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

use Magento\Framework\App\Config\Value;
use Magento\TestFramework\App\Config as AppConfig;

/** @var Value $config */
$config = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(Value::class);
$config->setPath('catalog/review/allow_guest');
$config->setScope('default');
$config->setScopeId(0);
$config->setValue(1);
$config->save();

/** @var AppConfig $appConfig */
$appConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(AppConfig::class);
$appConfig->clean();
