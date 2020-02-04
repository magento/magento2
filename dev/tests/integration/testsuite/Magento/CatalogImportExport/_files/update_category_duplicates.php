<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

require dirname(dirname(__DIR__)) . '/Catalog/_files/category_duplicates.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Catalog\Model\Category $category */
$categoryModel = $objectManager->create(\Magento\Catalog\Model\Category::class);
$categoryModel->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID);

$categoryModel->load(444)
    ->setName('Category 2-updated')
    ->save();
