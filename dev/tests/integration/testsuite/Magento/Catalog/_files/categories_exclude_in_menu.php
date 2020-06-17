<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Model\Category;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/categories.php');

$objectManager = Bootstrap::getObjectManager();

/** @var $category Category */
$category = $objectManager->create(Category::class);

$category->load(6);
$category->setIncludeInMenu(false);
$category->save();

$category->load(7);
$category->setIncludeInMenu(false);
$category->save();
