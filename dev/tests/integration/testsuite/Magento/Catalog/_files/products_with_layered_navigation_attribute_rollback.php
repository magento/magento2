<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\TestFramework\Catalog\Model\GetCategoryByName;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$objectManager = Bootstrap::getObjectManager();
/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);

foreach (['simple1', 'simple2', 'simple3'] as $sku) {
    try {
        $product = $productRepository->get($sku, false, null, true);
        $productRepository->delete($product);
    } catch (NoSuchEntityException $exception) {
        //Product already removed
    }
}

/** @var CategoryRepositoryInterface $categoryRepository */
$categoryRepository = $objectManager->get(CategoryRepositoryInterface::class);
/** @var GetCategoryByName $getCategoryByName */
$getCategoryByName = $objectManager->get(GetCategoryByName::class);
$category = $getCategoryByName->execute('Category 1');
try {
    if ($category->getId()) {
        $categoryRepository->delete($category);
    }
} catch (NoSuchEntityException $exception) {
    //Category already removed
}

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/layered_navigation_attribute_rollback.php');

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
