<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

require __DIR__ . '/../../../Magento/Catalog/_files/products_with_websites_and_stores.php';
require __DIR__ . '/../../../Magento/Customer/_files/customer_non_default_website_id.php';

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Wishlist\Model\Wishlist;

$objectManager = Bootstrap::getObjectManager();

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$simpleProduct = $productRepository->get('simple-2');

/* @var $wishlist Wishlist */
$wishlist = Bootstrap::getObjectManager()->create(Wishlist::class);
$wishlist->loadByCustomerId($customer->getId(), true);
$wishlist->addNewItem($simpleProduct);
$wishlist->setSharingCode('fixture_unique_code')
    ->setShared(1)
    ->save();
