<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class
);
$model->setName('system_attribute')->setId(2)->setEntityTypeId(4)->setIsUserDefined(0);
$model->save();
