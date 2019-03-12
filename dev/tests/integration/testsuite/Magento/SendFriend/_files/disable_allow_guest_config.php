<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Framework\App\Config\Value;
use Magento\TestFramework\Helper\Bootstrap;

/** @var Value $config */
$config = Bootstrap::getObjectManager()->create(Value::class);
$config->setPath('sendfriend/email/enabled');
$config->setScope('default');
$config->setScopeId(0);
$config->setValue(1);
$config->save();

/** @var Value $config */
$config = Bootstrap::getObjectManager()->create(Value::class);
$config->setPath('sendfriend/email/allow_guest');
$config->setScope('default');
$config->setScopeId(0);
$config->setValue(0);
$config->save();
