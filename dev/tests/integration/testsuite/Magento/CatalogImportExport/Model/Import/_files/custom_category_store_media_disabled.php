<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var $category \Magento\Catalog\Model\Category */
$category = $objectManager->create(\Magento\Catalog\Model\Category::class);
$category->isObjectNew(true);
$category->setName('MV')
    ->setParentId(2)
    ->setLevel(2)
    ->setIsActive(true)
    ->setPosition(1)
    ->save();

$website = $objectManager->create(\Magento\Store\Model\Website::class);
$website->setData(['code' => 'mascota', 'name' => 'mascota', 'default_group_id' => '1', 'is_default' => '0']);
$website->save();

$groupId = $objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)
    ->getWebsite()
    ->getDefaultGroupId();

$store = $objectManager->create(\Magento\Store\Model\Store::class)
    ->setCode('mascota')
    ->setWebsiteId($website->getId())
    ->setGroupId($groupId)
    ->setName('mascota')
    ->setIsActive(1)
    ->save();

$entityTypeCode = 'catalog_product';
$entityType     = $objectManager->create(\Magento\Eav\Model\Entity\Type::class)->loadByCode($entityTypeCode);
$defaultSetId   = $entityType->getDefaultAttributeSetId();

$attributeSet = $objectManager->create(\Magento\Eav\Model\Entity\Attribute\Set::class);
$data = [
    'attribute_set_name'    => 'vinos',
    'entity_type_id'        => $entityType->getId(),
    'sort_order'            => 200,
];
$attributeSet->setData($data);

$objectManager->create(\Magento\Eav\Model\AttributeSetManagement::class)
    ->create($entityTypeCode, $attributeSet, $defaultSetId);
