<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$attributeSetData = [
    'attribute_set_1_for_search',
    'attribute_set_2_for_search',
    'attribute_set_3_for_search',
    'attribute_set_4_for_search',
];

foreach ($attributeSetData as $attributeSetName) {
    /** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
    $attributeSet = $objectManager->create(\Magento\Eav\Model\Entity\Attribute\Set::class)
        ->load($attributeSetName, 'attribute_set_name');
    if ($attributeSet->getId()) {
        $attributeSet->delete();
    }
}
