<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

Bootstrap::getInstance()->loadArea('frontend');
$objectManager = Bootstrap::getObjectManager();

$simpleProduct = $objectManager->create(Product::class)
    ->setTypeId(Type::TYPE_SIMPLE)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Simple Product')
    ->setSku('simple-1')
    ->setPrice(10)
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setCategoryIds([2])
    ->setStockData([
        'use_config_manage_stock' => 1,
        'qty' => 100,
        'is_qty_decimal' => 0,
        'is_in_stock' => 1,
    ])
    ->save();

$productRepository = $objectManager->get(ProductRepositoryInterface::class);
$productRepository->save($simpleProduct);

$virtualProduct = $objectManager->create(Product::class)
    ->setTypeId(Type::TYPE_VIRTUAL)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Virtual Product')
    ->setSku('virtual-1')
    ->setPrice(10)
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setCategoryIds([2])
    ->setStockData([
        'use_config_manage_stock' => 1,
        'qty' => 100,
        'is_qty_decimal' => 0,
        'is_in_stock' => 1,
    ])
    ->save();

$productRepository->save($virtualProduct);

$addressData = include __DIR__ . '/address_data.php';
$billingAddress = $objectManager->create(Address::class, ['data' => $addressData]);
$billingAddress->setAddressType('billing');

/** @var Address $shippingAddress */
$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');

/** @var Quote $quote */
$quote = $objectManager->create(Quote::class);
$quote
    ->setCustomerIsGuest(true)
    ->setStoreId($objectManager->get(StoreManagerInterface::class)->getStore()->getId())
    ->setReservedOrderId('quoteWithVirtualProduct')
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->setCustomerEmail('test@test.magento.com');
$quote->addProduct($simpleProduct);
$quote->addProduct($virtualProduct);

$quote->getShippingAddress()->setShippingMethod('tablerate_bestway');
$quote->getPayment()->setMethod('checkmo');
$quote->collectTotals();

$quoteRepository = $objectManager->get(CartRepositoryInterface::class);
$quoteRepository->save($quote);

/** @var QuoteIdMask $quoteIdMask */
$quoteIdMask = $objectManager->create(QuoteIdMaskFactory::class)->create();
$quoteIdMask
    ->setQuoteId($quote->getId())
    ->setDataChanges(true)
    ->save();
