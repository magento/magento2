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
$productRepository = $objectManager
    ->create(ProductRepositoryInterface::class);
$firstProduct = $objectManager->create(Product::class);
$firstProduct->setTypeId(Type::TYPE_SIMPLE)
    ->setCategoryIds([3])
    ->setId(123)
    ->setAttributeSetId(4)
    ->setName('First Test Product For TableRate')
    ->setSku('tableRate-1')
    ->setPrice(40)
    ->setTaxClassId(0)
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(
        [
            'qty' => 100,
            'is_in_stock' => 1,
            'manage_stock' => 1,
        ]
    )
    ->save();

/** @var ProductRepositoryInterface $productRepository */
$firstProduct = $productRepository->save($firstProduct);

$secondProduct = $objectManager->create(Product::class);
$secondProduct->setTypeId(Type::TYPE_VIRTUAL)
    ->setCategoryIds([6])
    ->setId(124)
    ->setAttributeSetId(4)
    ->setName('Second Test Product For TableRate')
    ->setSku('tableRate-2')
    ->setPrice(20)
    ->setTaxClassId(0)
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED)
    ->setStockData(
        [
            'qty' => 100,
            'is_in_stock' => 1,
            'manage_stock' => 1,
        ]
    )
    ->save();

/** @var ProductRepositoryInterface $productRepository */
$secondProduct = $productRepository->save($secondProduct);

$addressData = include __DIR__ . '/address_data.php';
$billingAddress = $objectManager->create(
    Address::class,
    ['data' => $addressData]
);
$billingAddress->setAddressType('billing');

$shippingAddress = $objectManager->create(
    Address::class,
    ['data' => $addressData]
);
$shippingAddress->setAddressType('shipping');

$store = $objectManager
    ->get(StoreManagerInterface::class)
    ->getStore();

/** @var Quote $quote */
$quote = $objectManager->create(Quote::class);
$quote->setCustomerIsGuest(true)
    ->setStoreId($store->getId())
    ->setReservedOrderId('quoteWithVirtualProduct')
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress);
$quote->getPayment()->setMethod('checkmo');
$quote->addProduct($firstProduct);
$quote->addProduct($secondProduct);
$quote->setIsMultiShipping(0);

$quoteRepository = $objectManager->get(CartRepositoryInterface::class);
$quoteRepository->save($quote);

/** @var QuoteIdMask $quoteIdMask */
$quoteIdMask = $objectManager
    ->create(QuoteIdMaskFactory::class)
    ->create();
$quoteIdMask->setQuoteId($quote->getId())
    ->setDataChanges(true)
    ->save();
