<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Wishlist\Model\WishlistFactory;
use Magento\Framework\Serialize\SerializerInterface;

require __DIR__ . '/../../../Magento/Customer/_files/customer.php';
require __DIR__ . '/../../../Magento/Catalog/_files/product_simple.php';

$json = $objectManager->get(SerializerInterface::class);
$wishlistFactory = $objectManager->get(WishlistFactory::class);
$wishlist = $wishlistFactory->create();
$wishlist->loadByCustomerId($customer->getId(), true);
$wishlist->addNewItem($product, $json->serialize(['qty' => 3]));
