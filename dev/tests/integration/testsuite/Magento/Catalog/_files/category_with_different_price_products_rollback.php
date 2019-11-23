<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$categoryCollectionFactory = $objectManager->get(CollectionFactory::class);
$categoryRepository = $objectManager->get(CategoryRepositoryInterface::class);
try {
    $productRepository->deleteById('simple1000');
} catch (NoSuchEntityException $e) {
    //Already deleted.
}

try {
    $productRepository->deleteById('simple1001');
} catch (NoSuchEntityException $e) {
    //Already deleted.
}

try {
    $categoryCollection = $categoryCollectionFactory->create();
    $category = $categoryCollection
        ->addAttributeToFilter(CategoryInterface::KEY_NAME, 'Category 999')
        ->setPageSize(1)
        ->getFirstItem();
    $categoryRepository->delete($category);
} catch (NoSuchEntityException $e) {
    //Already deleted.
}
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
