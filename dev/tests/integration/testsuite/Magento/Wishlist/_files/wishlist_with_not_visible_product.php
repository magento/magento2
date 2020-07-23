<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Wishlist\Model\ResourceModel\Wishlist as WishlistResource;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\WishlistFactory;

require __DIR__ . '/../../../Magento/Customer/_files/customer.php';
require __DIR__ . '/../../../Magento/Catalog/_files/simple_products_not_visible_individually.php';

$objectManager = Bootstrap::getObjectManager();
/** @var WishlistResource $wishListResource */
$wishListResource = $objectManager->get(WishlistResource::class);
/** @var Wishlist $wishList */
$wishList = $objectManager->get(WishlistFactory::class)->create();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productRepository->cleanCache();
$product = $productRepository->get('simple_not_visible_1');
$wishList->loadByCustomerId($customer->getId(), true);
$item = $wishList->addNewItem($product);
$wishList->setSharingCode('fixture_unique_code');
$wishListResource->save($wishList);
