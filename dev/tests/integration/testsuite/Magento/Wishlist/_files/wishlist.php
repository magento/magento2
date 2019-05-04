<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/../../../Magento/Customer/_files/customer.php';
require __DIR__ . '/../../../Magento/Catalog/_files/product_simple.php';

$wishlist = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Wishlist\Model\Wishlist::class
);
$wishlist->loadByCustomerId($customer->getId(), true);
$item = $wishlist->addNewItem($product, new \Magento\Framework\DataObject([]));
$wishlist->setSharingCode('fixture_unique_code')->save();
