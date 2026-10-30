<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class
);
$model->setName('system_attribute')->setId(3)->setEntityTypeId(4)->setIsUserDefined(0)->setApplyTo(['simple']);
$model->save();
