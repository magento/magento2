<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

$model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Catalog\Model\ResourceModel\Eav\Attribute::class
);
$model->setName('user_attribute')->setId(1)->setEntityTypeId(4)->setIsUserDefined(1);
$model->save();
