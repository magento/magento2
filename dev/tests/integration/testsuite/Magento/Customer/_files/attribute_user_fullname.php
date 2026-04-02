<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Customer\Model\Attribute::class);
$model->loadByCode('customer', 'prefix')->setIsVisible('1');
$model->save();

$model->loadByCode('customer', 'middlename')->setIsVisible('1');
$model->save();

$model->loadByCode('customer', 'suffix')->setIsVisible('1');
$model->save();
