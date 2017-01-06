<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
$attributeGroup = $objectManager->create(\Magento\Eav\Model\Entity\Attribute\Group::class);
$entityTypeId = $objectManager->create(\Magento\Eav\Model\Entity\Type::class)->loadByCode('catalog_product')->getId();
$attributeGroup->setData([
    'attribute_group_name' => 'empty_attribute_group',
    'attribute_set_id' => 1,
]);
$attributeGroup->save();
