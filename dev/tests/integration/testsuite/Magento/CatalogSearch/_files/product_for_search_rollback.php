<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\ObjectManager;

/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();
/** @var EavSetupFactory $eavSetupFactory */
$eavSetupFactory = $objectManager->create(EavSetupFactory::class);
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

try {
    $productRepository->deleteById('simple_for_search');
} catch (NoSuchEntityException $e) {
    //Product already deleted.
}
/** @var EavSetup $eavSetup */
$eavSetup = $eavSetupFactory->create();
$eavSetup->removeAttribute(Product::ENTITY, 'test_searchable_attribute');

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
