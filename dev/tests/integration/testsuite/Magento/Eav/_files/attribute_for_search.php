<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Eav\Model\Entity\Type $entityType */
$entityType = $objectManager->create(\Magento\Eav\Model\Entity\Type::class)
    ->loadByCode('order');
$data = $entityType->getData();
$data['entity_type_code'] = 'test';
unset($data['entity_type_id']);
/** @var \Magento\Eav\Model\Entity\Type $testEntityType */
$testEntityType = $objectManager->create(\Magento\Eav\Model\Entity\Type::class)
    ->setData($data)
    ->save();
$entityTypeId = $testEntityType->getId();

/** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
$attributeSet = $objectManager->create(\Magento\Eav\Model\Entity\Attribute\Set::class);
$attributeSet->setData([
    'attribute_set_name' => 'test_attribute_set',
    'entity_type_id' => $entityTypeId,
    'sort_order' => 100,
]);
$attributeSet->validate();
$attributeSet->save();

/** @var \Magento\Eav\Model\Entity\Attribute\Group $attributeGroup */
$attributeGroup = $objectManager->create(\Magento\Eav\Model\Entity\Attribute\Group::class);
$attributeGroup->setData(
    [
        'attribute_set_id' => $attributeSet->getAttributeSetId(),
        'sort_order' => 30,
        'attribute_group_code' => 'test_attribute_group',
        'default_id' => 0,
    ]
);
$attributeGroup->save();

$attributeData = [
    [
        'attribute_code' => 'attribute_for_search_1',
        'entity_type_id' => $entityTypeId,
        'backend_type' => 'varchar',
        'is_required' => 1,
        'is_user_defined' => 1,
        'is_unique' => 0,
        'attribute_set_id' => $testEntityType->getDefaultAttributeSetId(),
        'attribute_group_id' => $attributeSet->getDefaultGroupId($testEntityType->getDefaultAttributeSetId()),
    ],
    [
        'attribute_code' => 'attribute_for_search_2',
        'entity_type_id' => $entityTypeId,
        'backend_type' => 'varchar',
        'is_required' => 1,
        'is_user_defined' => 1,
        'is_unique' => 0,
        'attribute_set_id' => $testEntityType->getDefaultAttributeSetId(),
        'attribute_group_id' => $attributeSet->getDefaultGroupId($testEntityType->getDefaultAttributeSetId()),
    ],
    [
        'attribute_code' => 'attribute_for_search_3',
        'entity_type_id' => $entityTypeId,
        'backend_type' => 'varchar',
        'is_required' => 1,
        'is_user_defined' => 1,
        'is_unique' => 0,
        'attribute_set_id' => $attributeSet->getAttributeSetId(),
        'attribute_group_id' => $attributeGroup->getAttributeGroupId(),
    ],
    [
        'attribute_code' => 'attribute_for_search_4',
        'entity_type_id' => $entityTypeId,
        'backend_type' => 'int',
        'is_required' => 0,
        'is_user_defined' => 1,
        'is_unique' => 0,
    ],
    [
        'attribute_code' => 'attribute_for_search_5',
        'entity_type_id' => $entityTypeId,
        'backend_type' => 'varchar',
        'is_required' => 0,
        'is_user_defined' => 1,
        'is_unique' => 0,
    ],
];

foreach ($attributeData as $data) {
    /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
    $attribute = $objectManager->create(\Magento\Eav\Model\Entity\Attribute::class);
    $attribute->setData($data);
    $attribute->save();
}
