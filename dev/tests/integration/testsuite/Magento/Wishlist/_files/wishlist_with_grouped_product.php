<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\DataObject;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\TestFramework\Workaround\Override\Fixture\Resolver;

Resolver::getInstance()->requireDataFixture('Magento/Customer/_files/customer.php');
Resolver::getInstance()->requireDataFixture(
    'Magento/GroupedProduct/_files/product_grouped_with_simple.php'
);

$objectManager = Bootstrap::getObjectManager();
/** @var CustomerRegistry $customerRegistry */
$customerRegistry = $objectManager->get(CustomerRegistry::class);
$customer = $customerRegistry->retrieve(1);
$wishlistFactory = $objectManager->get(WishlistFactory::class);
$wishlist = $wishlistFactory->create();
$wishlist->loadByCustomerId($customer->getId(), true);
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$product = $productRepository->get('grouped');
$simple1 = $productRepository->get('simple_11');
$simple2 = $productRepository->get('simple_22');
$buyRequest = new DataObject([
    'product' => $product->getId(),
    'super_group' =>
        [
            $simple1->getId() => '1',
            $simple2->getId() => '1',
        ],
    'action' => 'add',
]);
$wishlist->addNewItem($product, $buyRequest);
