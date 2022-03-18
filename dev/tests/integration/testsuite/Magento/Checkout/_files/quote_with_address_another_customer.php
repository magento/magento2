<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote\AddressFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/../../Customer/_files/customer_with_addresses.php';
require __DIR__ . '/../../../Magento/Catalog/_files/products.php';

$objectManager = Bootstrap::getObjectManager();

/** Import Customer Address Data */
$quoteShippingAddress = $objectManager->get(AddressFactory::class)->create();
$customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
$customer = $customerRepository->get('customer_with_addresses@test.com');
$addresses = $customer->getAddresses();
$addressFirst = array_shift($addresses);
$quoteShippingAddress->importCustomerAddressData($addressFirst);

$productRepository = $objectManager->create(ProductRepositoryInterface::class);
$product = $productRepository->get('simple');

$websiteRepository = $objectManager->get(WebsiteRepositoryInterface::class);
$mainWebsite = $websiteRepository->get('base');

$accountManagement = $objectManager->create(AccountManagementInterface::class);

$quoteRepository = $objectManager->get(CartRepositoryInterface::class);
$quote = $objectManager->get(QuoteFactory::class)->create();
$quote->setStoreId($mainWebsite->getDefaultStore()->getId())
    ->setIsActive(true)
    ->setIsMultiShipping(false)
    ->assignCustomerWithAddressChange($customer)
    ->setShippingAddress($quoteShippingAddress)
    ->setBillingAddress($quoteShippingAddress)
    ->setCheckoutMethod('customer')
    ->setPasswordHash($accountManagement->getPasswordHash('password'))
    ->setReservedOrderId('test_order_999')
    ->setCustomerEmail('aaa2@aaa.com')
    ->addProduct($product, 2);
$quoteRepository->save($quote);
