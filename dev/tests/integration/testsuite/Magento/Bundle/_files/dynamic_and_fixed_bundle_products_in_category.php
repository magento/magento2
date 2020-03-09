<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Helper\DefaultCategory;

require __DIR__ . '/product.php';
require __DIR__ . '/bundle_product_dropdown_options.php';
require __DIR__ . '/../../Catalog/_files/category.php';

/** @var CategoryLinkManagementInterface $categoryLinkManagement */
$categoryLinkManagement = $objectManager->create(CategoryLinkManagementInterface::class);
/** @var DefaultCategory $categoryHelper */
$categoryHelper = $objectManager->get(DefaultCategory::class);
$categoryLinkManagement->assignProductToCategories('bundle-product', [2, $category->getId()]);
$categoryLinkManagement->assignProductToCategories(
    'bundle-product-dropdown-options',
    [$categoryHelper->getId(), $category->getId()]
);
