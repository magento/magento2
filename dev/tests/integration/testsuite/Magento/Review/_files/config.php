<?php
/** @var Value $config */
use Magento\Framework\App\Config\Value;

$config = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(Value::class);
$config->setPath('catalog/review/allow_guest');
$config->setScope('default');
$config->setScopeId(0);
$config->setValue(1);
$config->save();
