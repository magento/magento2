<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Framework\Registry;
use Magento\TestFramework\Catalog\Model\GetCategoryByName;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

$objectManager = Bootstrap::getObjectManager();

/** @var Registry $registry */
$registry = $objectManager->get(Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

Resolver::getInstance()->requireDataFixture('Magento/CatalogUrlRewrite/_files/product_with_category.php');

/** @var GetCategoryByName $getCategoryByName */
$getCategoryByName = $objectManager->create(GetCategoryByName::class);
$category = $getCategoryByName->execute('category 1');
/** @var CategoryLinkManagementInterface $linkManagement */
$linkManagement = $objectManager->get(CategoryLinkManagementInterface::class);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$product = $productRepository->get('p002');
$product->setStoreId(1);
$product->setUrlKey('store-1-key');
$product = $productRepository->save($product);
$linkManagement->assignProductToCategories($product->getSku(), [Category::TREE_ROOT_ID, $category->getEntityId()]);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);
