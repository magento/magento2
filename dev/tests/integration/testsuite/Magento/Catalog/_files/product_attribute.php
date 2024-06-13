<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

/** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
$attribute = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Catalog\Model\ResourceModel\Eav\Attribute::class);
$attribute->setAttributeCode('test_attribute_code_333')
    ->setEntityTypeId(4)
    ->setIsGlobal(1)
    ->setPrice(95)
    ->setIsUserDefined(1);
$attribute->save();
