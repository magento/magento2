<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

/** @var Value $config */
use Magento\Framework\App\Config\Value;
use Magento\TestFramework\App\Config as AppConfig;

$config = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(Value::class);
$config->setPath('catalog/review/allow_guest');
$config->setScope('default');
$config->setScopeId(0);
$config->setValue(0);
$config->save();

/** @var AppConfig $appConfig */
$appConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(AppConfig::class);
$appConfig->clean();
