<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Store/_files/core_fixturestore_rollback.php');

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

//Remove category
/** @var $category \Magento\Catalog\Model\Category */
$category = $objectManager->create(\Magento\Catalog\Model\Category::class);
$category->load(96377);
if ($category->getId()) {
    $category->delete();
}

$productSkuList = ['simple_1', 'simple_2', 'simple_3'];
foreach ($productSkuList as $sku) {
    try {
        $productRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $product = $productRepository->get($sku, true);
        if ($product->getId()) {
            $productRepository->delete($product);
        }
    } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
        //Product already removed
    }
}

/** @var Magento\Store\Model\Website $website */
$website = $objectManager->get(Magento\Store\Model\Website::class);
$website->load('second_website', 'code');
if ($website->getId()) {
    $website->delete();
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
