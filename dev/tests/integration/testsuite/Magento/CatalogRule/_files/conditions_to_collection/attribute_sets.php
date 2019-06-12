<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$attributeSetFactory = $objectManager->get(\Magento\Eav\Api\Data\AttributeSetInterfaceFactory::class);
$dataObjectHelper = $objectManager->get(\Magento\Framework\Api\DataObjectHelper::class);
$attributeSetRepository = $objectManager->get(\Magento\Catalog\Api\AttributeSetRepositoryInterface::class);
$attributeSetManagement = $objectManager->get(\Magento\Eav\Api\AttributeSetManagementInterface::class);

$entityTypeId = $objectManager->create(\Magento\Eav\Model\Entity\Type::class)->loadByCode('catalog_product')->getId();
$defaultAttributeSet = $objectManager->get(Magento\Eav\Model\Config::class)
    ->getEntityType('catalog_product')
    ->getDefaultAttributeSetId();

$attributeSet = $attributeSetFactory->create();
$dataObjectHelper->populateWithArray(
    $attributeSet,
    [
        'attribute_set_name' => 'Super Powerful Muffins',
        'entity_type_id' => $entityTypeId,
    ],
    \Magento\Eav\Api\Data\AttributeSetInterface::class
);
$attributeSetManagement->create('catalog_product', $attributeSet, $defaultAttributeSet)->save();


$attributeSet = $attributeSetFactory->create();
$dataObjectHelper->populateWithArray(
    $attributeSet,
    [
        'attribute_set_name' => 'Banana Rangers',
        'entity_type_id' => $entityTypeId,
    ],
    \Magento\Eav\Api\Data\AttributeSetInterface::class
);
$attributeSetManagement->create('catalog_product', $attributeSet, $defaultAttributeSet)->save();

$attributeSet = $attributeSetFactory->create();
$dataObjectHelper->populateWithArray(
    $attributeSet,
    [
        'attribute_set_name' => 'Guardians of the Refrigerator',
        'entity_type_id' => $entityTypeId,
    ],
    \Magento\Eav\Api\Data\AttributeSetInterface::class
);
$attributeSetManagement->create('catalog_product', $attributeSet, $defaultAttributeSet)->save();
