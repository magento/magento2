<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Customer\Model\Attribute');
$model->loadByCode('customer', 'prefix')->setIsVisible('1');
$model->save();

$model->loadByCode('customer', 'middlename')->setIsVisible('1');
$model->save();

$model->loadByCode('customer', 'suffix')->setIsVisible('1');
$model->save();
