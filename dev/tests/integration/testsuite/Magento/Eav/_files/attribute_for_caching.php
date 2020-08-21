<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Eav\Model\Entity\Type $entityType */
$entityType = $objectManager->create(\Magento\Eav\Model\Entity\Type::class)
    ->loadByCode('catalog_product');
$data = $entityType->getData();
$entityTypeId = $entityType->getId();

/** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
$attributeSet = $objectManager->create(\Magento\Eav\Model\Entity\Attribute\Set::class);
$attributeSet->setData(
    [
        'attribute_set_name' => 'test_attribute_set',
        'entity_type_id' => $entityTypeId,
        'sort_order' => 100
    ]
);
$attributeSet->validate();
$attributeSet->save();

$attributeData = [
    [
        'attribute_code' => 'foo',
        'entity_type_id' => $entityTypeId,
        'backend_type' => 'varchar',
        'is_required' => 1,
        'is_user_defined' => 1,
        'is_unique' => 0,
        'frontend_label' => ['foo'],
        'attribute_set_id' => $entityType->getDefaultAttributeSetId()
    ]
];

foreach ($attributeData as $data) {
    /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
    $attribute = $objectManager->create(\Magento\Eav\Model\Entity\Attribute::class);
    $attribute->setData($data);
    $attribute->save();
}
