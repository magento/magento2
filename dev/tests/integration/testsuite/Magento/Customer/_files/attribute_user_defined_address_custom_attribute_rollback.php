<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

/** @var \Magento\Customer\Model\Attribute $attributeModel */
$attributeModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Customer\Model\Attribute::class
);
$attributeModel->load('custom_attribute1', 'attribute_code')->delete();
$attributeModel->load('custom_attribute2', 'attribute_code')->delete();
