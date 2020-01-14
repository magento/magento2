<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\QuoteRepository;
use Magento\TestFramework\Helper\Bootstrap;

require 'fixed_discount.php';

/** @var ObjectManagerInterface $objectManager */
$objectManager = Bootstrap::getObjectManager();

$addressData = [
    'firstname' => 'John',
    'lastname' => 'Doe',
    'company' => '',
    'email' => 'test@com.com',
    'street' => [
        0 => 'test1',
    ],
    'city' => 'Test',
    'region_id' => '1',
    'region' => '',
    'postcode' => '9001',
    'country_id' => 'US',
    'telephone' => '11111111',
];
/** @var Address $billingAddress */
$billingAddress = $objectManager->create(Address::class, ['data' => $addressData]);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setAddressType('shipping')
    ->setId(null);

/** @var Quote $quote */
$quote = $objectManager->create(Quote::class);
$quote->setCustomerIsGuest(true)
    ->setReservedOrderId('100000015')
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress);

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->get(ProductRepositoryInterface::class);
for ($i = 1; $i <= 3; $i++) {
    /** @var ProductInterface $product */
    $product = $objectManager->create(ProductInterface::class);
    $product->setTypeId('simple')
        ->setName('Simple ' . $i)
        ->setSku('simple' . $i)
        ->setAttributeSetId(4)
        ->setPrice(5.69 + $i * 2)
        ->setWeight(1);

    /** @var StockItemInterface $stockItem */
    $stockItem = $objectManager->create(StockItemInterface::class);
    $stockItem->setQty(10)
        ->setIsInStock(true);
    $extensionAttributes = $product->getExtensionAttributes();
    $extensionAttributes->setStockItem($stockItem);

    $item = $productRepository->save($product);
    $quote->addProduct($item, $i);
}

$quote->setCouponCode($coupon->getCode());
$quote->collectTotals();

/** @var QuoteRepository $quoteRepository */
$quoteRepository = $objectManager->get(QuoteRepository::class);
$quoteRepository->save($quote);
