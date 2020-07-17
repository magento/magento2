<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/../../Customer/_files/customer.php';
require __DIR__ . '/../../Customer/_files/customer_address.php';
require __DIR__ . '/../../../Magento/Catalog/_files/simple_products_not_visible_individually.php';

$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
/** @var CartRepositoryInterface $quoteRepository */
$quoteRepository = $objectManager->get(CartRepositoryInterface::class);
/** @var AddressInterface $quoteShippingAddress */
$quoteShippingAddress = $objectManager->get(AddressInterfaceFactory::class)->create();
/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
/** @var AddressRepositoryInterface $addressRepository */
$addressRepository = $objectManager->get(AddressRepositoryInterface::class);
$quoteShippingAddress->importCustomerAddressData($addressRepository->getById(1));
$customer = $customerRepository->getById(1);

/** @var CartInterface $quote */
$quote = $objectManager->get(CartInterfaceFactory::class)->create();
$quote->setStoreId(1)
    ->setIsActive(true)
    ->setIsMultiShipping(0)
    ->assignCustomerWithAddressChange($customer)
    ->setShippingAddress($quoteShippingAddress)
    ->setBillingAddress($quoteShippingAddress)
    ->setCheckoutMethod(Onepage::METHOD_CUSTOMER)
    ->setReservedOrderId('test_order_with_not_visible_product')
    ->setEmail($customer->getEmail())
    ->addProduct($productRepository->get('simple_not_visible_1'), 1);

$quoteRepository->save($quote);
