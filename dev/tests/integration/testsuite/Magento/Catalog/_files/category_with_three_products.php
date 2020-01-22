<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/category_with_different_price_products.php';
require __DIR__ . '/second_product_simple.php';

$objectManager = Bootstrap::getObjectManager();
/** @var CategoryLinkManagementInterface $categoryLinkManagement */
$categoryLinkManagement = $objectManager->get(CategoryLinkManagementInterface::class);
$categoryLinkManagement->assignProductToCategories('simple2', [$category->getId()]);
