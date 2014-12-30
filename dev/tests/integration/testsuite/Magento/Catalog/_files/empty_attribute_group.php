<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
$attributeGroup = $objectManager->create('Magento\Eav\Model\Entity\Attribute\Group');
$entityTypeId = $objectManager->create('Magento\Eav\Model\Entity\Type')->loadByCode('catalog_product')->getId();
$attributeGroup->setData([
    'attribute_group_name' => 'empty_attribute_group',
    'attribute_set_id' => 1,
]);
$attributeGroup->save();
