<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
$attributeSet = $objectManager->create(\Magento\Eav\Model\Entity\Attribute\Set::class)
    ->load('Super Powerful Muffins', 'attribute_set_name');
if ($attributeSet->getId()) {
    $attributeSet->delete();
}

$attributeSet = $objectManager->create(\Magento\Eav\Model\Entity\Attribute\Set::class)
    ->load('Banana Rangers', 'attribute_set_name');
if ($attributeSet->getId()) {
    $attributeSet->delete();
}

$attributeSet = $objectManager->create(\Magento\Eav\Model\Entity\Attribute\Set::class)
    ->load('Guardians of the Refrigerator', 'attribute_set_name');
if ($attributeSet->getId()) {
    $attributeSet->delete();
}
