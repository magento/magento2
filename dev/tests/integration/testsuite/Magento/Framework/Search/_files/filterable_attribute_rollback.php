<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/* Create attribute */
/** @var $installer \Magento\Catalog\Setup\CategorySetup */
$installer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Catalog\Setup\CategorySetup',
    ['resourceName' => 'catalog_setup']
);
/** @var $attribute \Magento\Catalog\Model\Resource\Eav\Attribute */
$attribute = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Catalog\Model\Resource\Eav\Attribute'
);
$attribute->loadByCode($installer->getEntityTypeId('catalog_product'), 'select_attribute');
if ($attribute->getId()) {
    $attribute->delete();
}

$attribute->loadByCode($installer->getEntityTypeId('catalog_product'), 'multiselect_attribute');
if ($attribute->getId()) {
    $attribute->delete();
}
