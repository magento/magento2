<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/** @var $objectManager \Magento\TestFramework\ObjectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$registry = $objectManager->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var \Magento\Catalog\Model\Product $product */
$product = $objectManager->create(\Magento\Catalog\Model\ProductRepository::class)->get('simple');
/** @var \Magento\Catalog\Model\ResourceModel\Product $productResource */
$productResource = $objectManager->create(\Magento\Catalog\Model\ResourceModel\Product::class);
$productResource->delete($product);

$eavSetupFactory = $objectManager->create(\Magento\Eav\Setup\EavSetupFactory::class);
/** @var \Magento\Eav\Setup\EavSetup $eavSetup */
$eavSetup = $eavSetupFactory->create();
$eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'test_searchable_attribute');

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
