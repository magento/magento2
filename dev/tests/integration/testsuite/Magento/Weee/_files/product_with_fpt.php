<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

$installer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Catalog\Model\Resource\Setup',
    ['resourceName' => 'catalog_setup']
);
$attributeSetId = $installer->getAttributeSetId('catalog_product', 'Default');
$entityModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Eav\Model\Entity');
$entityTypeId = $entityModel->setType(\Magento\Catalog\Model\Product::ENTITY)->getTypeId();
$groupId = $installer->getDefaultAttributeGroupId($entityTypeId, $attributeSetId);

$attribute = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Catalog\Model\Resource\Eav\Attribute'
);
$attribute->setAttributeCode(
    'fpt_for_all'
)->setEntityTypeId(
    $entityTypeId
)->setAttributeGroupId(
    $groupId
)->setAttributeSetId(
    $attributeSetId
)->setFrontendInput(
    'weee'
)->setIsUserDefined(
    1
)->save();

$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
$product->setTypeId(
    'simple'
)->setId(
    1
)->setAttributeSetId(
    $attributeSetId
)->setStoreId(
    1
)->setWebsiteIds(
    [1]
)->setName(
    'Simple Product'
)->setSku(
    'simple'
)->setPrice(
    100
)->setFptForAll(
    [['website_id' => 0, 'country' => 'US', 'state' => 0, 'price' => 0.07, 'delete' => '']]
)->save();
