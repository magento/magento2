<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Eav\Model\Entity\Attribute\Group $attributeSet */
$attributeGroup = $objectManager->create('Magento\Eav\Model\Entity\Attribute\Group')
    ->load('empty_attribute_group', 'attribute_group_name');
if ($attributeGroup->getId()) {
    $attributeGroup->delete();
}

$attributeGroupUpdated = $objectManager->create('Magento\Eav\Model\Entity\Attribute\Group')
    ->load('empty_attribute_group_updated', 'attribute_group_name');
if ($attributeGroupUpdated->getId()) {
    $attributeGroupUpdated->delete();
}
