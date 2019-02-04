<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/** @var \Magento\Framework\ObjectManagerInterface $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Framework\Registry $registry */
$registry = $objectManager->get(\Magento\Framework\Registry::class);
$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

/** @var \Magento\Wishlist\Model\Wishlist $wishlist */
$wishlist = $objectManager->create(\Magento\Wishlist\Model\Wishlist::class);

/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
try {
    $firstCustomer = $customerRepository->get('customer@example.com');
    $wishlist->loadByCustomerId($firstCustomer->getId());
    $wishlist->delete();
    $secondCustomer = $customerRepository->get('customer_two@example.com');
    $wishlist->loadByCustomerId($secondCustomer->getId());
    $wishlist->delete();
} catch (NoSuchEntityException $e) {
    /** Tests which are wrapped with MySQL transaction clear all data by transaction rollback. */
}

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', false);

require __DIR__ . '/../../../Magento/Customer/_files/two_customers_rollback.php';
require __DIR__ . '/../../../Magento/Catalog/_files/product_simple_rollback.php';
