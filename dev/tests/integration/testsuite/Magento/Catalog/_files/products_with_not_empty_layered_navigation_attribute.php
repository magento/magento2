<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Catalog\Model\GetCategoryByName;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture(
    'Magento/Catalog/_files/products_with_layered_navigation_attribute.php'
);

$objectManager = Bootstrap::getObjectManager();
/** @var StoreManagerInterface $storeManager */
$storeManager = $objectManager->get(StoreManagerInterface::class);
/** @var ProductAttributeRepositoryInterface $attributeRepository */
$attributeRepository = $objectManager->create(ProductAttributeRepositoryInterface::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);
/** @var CategoryRepositoryInterface $categoryRepository */
$categoryRepository = $objectManager->create(CategoryRepositoryInterface::class);
/** @var GetCategoryByName $getCategoryByName */
$getCategoryByName = $objectManager->create(GetCategoryByName::class);
$category = $getCategoryByName->execute('Category 1');
$attribute = $attributeRepository->get('test_configurable');

$firstProduct = $productRepository->get('simple1');
$firstProduct->setData('test_configurable', $attribute->getSource()->getOptionId('Option 1'));
$productRepository->save($firstProduct);

$secondProduct = $productRepository->get('simple2');
$secondProduct->setData('test_configurable', $attribute->getSource()->getOptionId('Option 2'));
$productRepository->save($secondProduct);

$thirdProduct = $productRepository->get('simple3');
$thirdProduct->setData('test_configurable', $attribute->getSource()->getOptionId('Option 2'));
$thirdProduct->setStatus(Status::STATUS_ENABLED);
$productRepository->save($thirdProduct);

$oldStoreId = $storeManager->getStore()->getId();
$storeManager->setCurrentStore(Store::DEFAULT_STORE_ID);
$category->addData(['available_sort_by' => 'position,name,price,test_configurable']);
try {
    $categoryRepository->save($category);
} finally {
    $storeManager->setCurrentStore($oldStoreId);
}
