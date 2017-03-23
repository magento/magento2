<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
$attributeSet = $objectManager->create(\Magento\Eav\Model\Entity\Attribute\Set::class)
    ->load('empty_attribute_set', 'attribute_set_name');
if ($attributeSet->getId()) {
    $attributeSet->delete();
}
