<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Customer\Model\Attribute');
$model->loadByCode('customer', 'prefix')->setIsVisible('1');
$model->save();

$model->loadByCode('customer', 'middlename')->setIsVisible('1');
$model->save();

$model->loadByCode('customer', 'suffix')->setIsVisible('1');
$model->save();
