<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/category_duplicates.php');

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Catalog\Model\Category $category */
$categoryModel = $objectManager->create(\Magento\Catalog\Model\Category::class);
$categoryModel->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID);

$categoryModel->load(444)
    ->setName('Category 2-updated')
    ->save();
