<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Helper\DefaultCategory;
use Magento\TestFramework\Catalog\Model\GetCategoryByName;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Bundle/_files/product.php');
Resolver::getInstance()->requireDataFixture('Magento/Bundle/_files/bundle_product_dropdown_options.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/category.php');

$objectManager = Bootstrap::getObjectManager();
/** @var GetCategoryByName $getCategoryByName */
$getCategoryByName = $objectManager->create(GetCategoryByName::class);
$category = $getCategoryByName->execute('Category 1');
/** @var CategoryLinkManagementInterface $categoryLinkManagement */
$categoryLinkManagement = $objectManager->create(CategoryLinkManagementInterface::class);
/** @var DefaultCategory $categoryHelper */
$categoryHelper = $objectManager->get(DefaultCategory::class);
$categoryLinkManagement->assignProductToCategories('bundle-product', [2, $category->getId()]);
$categoryLinkManagement->assignProductToCategories(
    'bundle-product-dropdown-options',
    [$categoryHelper->getId(), $category->getId()]
);
