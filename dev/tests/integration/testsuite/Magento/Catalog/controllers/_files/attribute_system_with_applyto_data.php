<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Catalog\Model\ResourceModel\Eav\Attribute'
);
$model->setName('system_attribute')->setId(3)->setEntityTypeId(4)->setIsUserDefined(0)->setApplyTo(['simple']);
$model->save();
