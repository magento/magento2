<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Type\Onepage;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Api\Data\CartInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/../../Customer/_files/customer.php';
require __DIR__ . '/../../../Magento/Catalog/_files/taxable_simple_product.php';

$objectManager = Bootstrap::getObjectManager();
/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productRepository->cleanCache();
/** @var CartRepositoryInterface $quoteRepository */
$quoteRepository = $objectManager->get(CartRepositoryInterface::class);
/** @var CustomerRepositoryInterface $customerRepository */
$customerRepository = $objectManager->get(CustomerRepositoryInterface::class);
$customer = $customerRepository->get('customer@example.com');

/** @var CartInterface $quote */
$quote = $objectManager->get(CartInterfaceFactory::class)->create();
$quote->setStoreId(1)
    ->setIsActive(false)
    ->setIsMultiShipping(0)
    ->setCustomer($customer)
    ->setCheckoutMethod(Onepage::METHOD_CUSTOMER)
    ->setReservedOrderId('test_order_with_customer_inactive_quote')
    ->addProduct($productRepository->get('taxable_product'), 1);
$quoteRepository->save($quote);
