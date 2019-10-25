<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/category_product.php';
require __DIR__ . '/second_product_simple.php';

$categoryLinkManagement = Bootstrap::getObjectManager()->create(CategoryLinkManagementInterface::class);
$categoryLinkManagement->assignProductToCategories('simple2', [333]);
