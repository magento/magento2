<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$model = $objectManager->create('Magento\Customer\Model\Attribute');
$model->load('date', 'attribute_code')->delete();
