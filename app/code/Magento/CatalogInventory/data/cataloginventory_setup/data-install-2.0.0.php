<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/* @var $installer \Magento\Eav\Model\Entity\Setup */
$installer = $this;

$installer->getConnection()
    ->insertForce(
        $installer->getTable('cataloginventory_stock'),
        ['stock_id' => 1, 'stock_name' => 'Default']
    );

/** @var $this \Magento\Catalog\Model\Resource\Setup */

$groupName = 'Product Details';
$entityTypeId = $this->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
$attributeSetId = $this->getAttributeSetId($entityTypeId, 'Default');

$attribute = $this->getAttribute($entityTypeId, 'quantity_and_stock_status');
if ($attribute) {
    $this->addAttributeToGroup($entityTypeId, $attributeSetId, $groupName, $attribute['attribute_id'], 60);
    $this->updateAttribute($entityTypeId, $attribute['attribute_id'], 'default_value', 1);
}
