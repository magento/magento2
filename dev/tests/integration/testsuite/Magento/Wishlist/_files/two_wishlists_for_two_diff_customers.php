<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

require __DIR__ . '/../../../Magento/Customer/_files/two_customers.php';
require __DIR__ . '/../../../Magento/Catalog/_files/product_simple.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$customerRepository = $objectManager->create(\Magento\Customer\Api\CustomerRepositoryInterface::class);
$firstCustomer = $customerRepository->get('customer@example.com');

$wishlistForFirstCustomer = $objectManager->create(\Magento\Wishlist\Model\Wishlist::class);
$wishlistForFirstCustomer->loadByCustomerId($firstCustomer->getId(), true);
$item = $wishlistForFirstCustomer->addNewItem($product, new \Magento\Framework\DataObject([]));
$wishlistForFirstCustomer->save();

$secondCustomer = $customerRepository->get('customer_two@example.com');
$wishlistForSecondCustomer = $objectManager->create(\Magento\Wishlist\Model\Wishlist::class);
$wishlistForSecondCustomer->loadByCustomerId($secondCustomer->getId(), true);
$item = $wishlistForSecondCustomer->addNewItem($product, new \Magento\Framework\DataObject([]));
$wishlistForSecondCustomer->save();
