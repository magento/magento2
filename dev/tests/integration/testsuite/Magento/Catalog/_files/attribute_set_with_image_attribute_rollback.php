<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/** @var $product \Magento\Catalog\Model\Product */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$entityType = $objectManager->create('Magento\Eav\Model\Entity\Type')->loadByCode('catalog_product');

// remove attribute

/** @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection $attributeCollection */
$attributeCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection');
$attributeCollection->setFrontendInputTypeFilter('media_image');
$attributeCollection->setCodeFilter('funny_image');
$attributeCollection->setEntityTypeFilter($entityType->getId());
$attributeCollection->setPageSize(1);
$attributeCollection->load();
$attribute = $attributeCollection->fetchItem();
$attribute->delete();

// remove attribute set

/** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $attributeSetCollection */
$attributeSetCollection = $objectManager->create('Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection');
$attributeSetCollection->addFilter('attribute_set_name', 'attribute_set_with_media_attribute');
$attributeSetCollection->addFilter('entity_type_id', $entityType->getId());
$attributeSetCollection->setOrder('attribute_set_id'); // descending is default value
$attributeSetCollection->setPageSize(1);
$attributeSetCollection->load();

/** @var \Magento\Eav\Model\Entity\Attribute\Set $attributeSet */
$attributeSet = $attributeSetCollection->fetchItem();
$attributeSet->delete();
