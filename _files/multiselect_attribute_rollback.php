<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Catalog\Setup\CategorySetup;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

/** @var $installer CategorySetup */
$installer = Bootstrap::getObjectManager()->create(
    CategorySetup::class,
    ['resourceName' => 'catalog_setup']
);
$registry = Bootstrap::getObjectManager()->get(Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$productRepository = Bootstrap::getObjectManager()->get(ProductRepository::class);
$product = $productRepository->get('simple_product_with_multiselect_attribute');
$product->delete();

/** @var $attribute Attribute */
$attribute = Bootstrap::getObjectManager()->create(Attribute::class);
$attribute->loadByCode($installer->getEntityTypeId('catalog_product'), 'multiselect_attribute');
if ($attribute->getId()) {
    $attribute->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
