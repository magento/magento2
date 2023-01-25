<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/category_product.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/second_product_simple.php');

$categoryLinkManagement = Bootstrap::getObjectManager()->create(CategoryLinkManagementInterface::class);
$categoryLinkManagement->assignProductToCategories('simple2', [333]);
