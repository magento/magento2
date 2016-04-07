<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
$attribute = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Catalog\Model\ResourceModel\Eav\Attribute'
);
$attribute->load('select_attribute', 'attribute_code');
if ($attribute->getId()) {
    $attribute->delete();
}
