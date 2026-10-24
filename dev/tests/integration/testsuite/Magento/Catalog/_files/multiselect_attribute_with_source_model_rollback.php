<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
/* Delete attribute  with multiselect_attribute code */
$registry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Registry');
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
/** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
$attribute = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Catalog\Model\ResourceModel\Eav\Attribute'
);
$attribute->load('multiselect_attr_with_source', 'attribute_code');
$attribute->delete();

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
