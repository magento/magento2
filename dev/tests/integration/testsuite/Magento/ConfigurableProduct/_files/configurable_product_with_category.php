<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Helper\DefaultCategory;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/category.php');
Resolver::getInstance()->requireDataFixture('Magento/ConfigurableProduct/_files/product_configurable.php');

$objectManager = Bootstrap::getObjectManager();
/** @var CategoryLinkManagementInterface $categoryLinkManagement */
$categoryLinkManagement = $objectManager->create(CategoryLinkManagementInterface::class);
/** @var DefaultCategory $categoryHelper */
$categoryHelper = $objectManager->get(DefaultCategory::class);

foreach (['simple_10', 'simple_20', 'configurable'] as $sku) {
    $categoryLinkManagement->assignProductToCategories($sku, [$categoryHelper->getId(), 333]);
}
