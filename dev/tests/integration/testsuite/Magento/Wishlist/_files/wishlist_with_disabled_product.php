<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Wishlist\Model\ResourceModel\Wishlist as WishlistResource;
use Magento\Wishlist\Model\Wishlist;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/customer.php');
Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/simple_product_disabled.php');

$objectManager = Bootstrap::getObjectManager();
/** @var WishlistResource $wishListResource */
$wishListResource = $objectManager->get(WishlistResource::class);
/** @var Wishlist $wishlist */
$wishlist = $objectManager->get(WishlistFactory::class)->create();
/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
$customer = $customerRepository->get('customer@example.com');
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productRepository->cleanCache();
$product = $productRepository->get('product_disabled');
$wishlist->loadByCustomerId($customer->getId(), true);
$item = $wishlist->addNewItem($product);
$wishlist->setSharingCode('wishlist_disabled_item');
$wishListResource->save($wishlist);
