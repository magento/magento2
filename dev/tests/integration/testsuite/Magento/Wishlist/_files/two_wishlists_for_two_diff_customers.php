<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

require __DIR__ . '/../../../Magento/Customer/_files/two_customers.php';
require __DIR__ . '/../../../Magento/Catalog/_files/product_simple.php';

$firstCustomerIdFromFixture = 1;
$wishlistForFirstCustomer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Wishlist\Model\Wishlist::class
);
$wishlistForFirstCustomer->loadByCustomerId($firstCustomerIdFromFixture, true);
$item = $wishlistForFirstCustomer->addNewItem($product, new \Magento\Framework\DataObject([]));
$wishlistForFirstCustomer->save();

$secondCustomerIdFromFixture = 2;
$wishlistForSecondCustomer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Wishlist\Model\Wishlist::class
);
$wishlistForSecondCustomer->loadByCustomerId($secondCustomerIdFromFixture, true);
$item = $wishlistForSecondCustomer->addNewItem($product, new \Magento\Framework\DataObject([]));
$wishlistForSecondCustomer->save();
