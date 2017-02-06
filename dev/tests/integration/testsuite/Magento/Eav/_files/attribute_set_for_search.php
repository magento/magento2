<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$entityTypeId = $objectManager->create(\Magento\Eav\Model\Entity\Type::class)
    ->loadByCode('catalog_product')
    ->getId();

$attributeSetData = [
    [
        'attribute_set_name' => 'attribute_set_1_for_search',
        'entity_type_id' => $entityTypeId,
        'sort_order' => 100,
    ],
    [
        'attribute_set_name' => 'attribute_set_2_for_search',
        'entity_type_id' => $entityTypeId,
        'sort_order' => 200,
    ],
    [
        'attribute_set_name' => 'attribute_set_3_for_search',
        'entity_type_id' => $entityTypeId,
        'sort_order' => 300,
    ],
    [
        'attribute_set_name' => 'attribute_set_4_for_search',
        'entity_type_id' => $entityTypeId,
        'sort_order' => 400,
    ],
];

foreach ($attributeSetData as $data) {
    /** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
    $attributeSet = $objectManager->create(\Magento\Eav\Model\Entity\Attribute\Set::class);
    $attributeSet->setData($data);
    $attributeSet->validate();
    $attributeSet->save();
}
