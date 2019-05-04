<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Eav\Model\Entity\Type $entityType */
$entityType = $objectManager->create(\Magento\Eav\Model\Entity\Type::class);
$entityType->loadByCode('catalog_product');
$entityTypeId = $entityType->getId();

/** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
$attributeSet = $objectManager->create(\Magento\Eav\Model\Entity\Attribute\Set::class);
$attributeSet->load('default', 'attribute_set_name');
$attributeSetId = $attributeSet->getId();

$attributeGroupId = $attributeSet->getDefaultGroupId($entityType->getDefaultAttributeSetId());

$attributeData = [
    [
        'attribute_code' => 'test_attribute',
        'entity_type_id' => $entityTypeId,
        'backend_type' => 'varchar',
        'is_required' => 1,
        'is_user_defined' => 1,
        'is_unique' => 0,
        'attribute_set_id' => $attributeSetId,
        'attribute_group_id' => $attributeGroupId,
    ],
];

foreach ($attributeData as $data) {
    /** @var \Magento\Eav\Model\Entity\Attribute $attribute */
    $attribute = $objectManager->create(\Magento\Eav\Model\Entity\Attribute::class);
    $attribute->setData($data);
    $attribute->setIsStatic(true);
    $attribute->save();
}
