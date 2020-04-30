<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Wishlist\Model\WishlistFactory;

require __DIR__ . '/../../../Magento/Customer/_files/customer.php';
require __DIR__ . '/../../../Magento/ConfigurableProduct/_files/configurable_product_with_two_child_products.php';

$wishlistFactory = $objectManager->get(WishlistFactory::class);
$wishlist = $wishlistFactory->create();
$wishlist->loadByCustomerId($customer->getId(), true);
$product = $productRepository->get('Configurable product');
$wishlist->addNewItem($product);
