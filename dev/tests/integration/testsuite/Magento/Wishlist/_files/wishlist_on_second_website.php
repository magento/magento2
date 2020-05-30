<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Wishlist\Model\Wishlist;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Catalog/_files/products_with_websites_and_stores.php');
Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/customer_non_default_website_id.php');

$objectManager = Bootstrap::getObjectManager();
$objectManager = Bootstrap::getObjectManager();
/** @var CustomerRegistry $customerRegistry */
$customerRegistry = Bootstrap::getObjectManager()->create(CustomerRegistry::class);
$customer = $customerRegistry->retrieve(1);
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
