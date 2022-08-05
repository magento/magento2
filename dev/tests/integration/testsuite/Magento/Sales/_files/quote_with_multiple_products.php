<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\CatalogInventory\Api\Data\StockItemInterface;

Bootstrap::getInstance()->loadArea('frontend');
/** @var ObjectManager $objectManager */
$objectManager = Bootstrap::getObjectManager();
$productRepository = $objectManager
    ->create(ProductRepositoryInterface::class);
$firstProduct = $objectManager->create(Product::class);
$firstProduct->setTypeId('simple')
    ->setCategoryIds([3])
    ->setId(123)
    ->setAttributeSetId(4)
    ->setName('First Test Product For TableRate')
    ->setSku('simple-tableRate-1')
    ->setPrice(30)
    ->setTaxClassId(0)
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED);
/** @var StockItemInterface $stockItem */
$firstStockItem = $objectManager->create(StockItemInterface::class);
$firstStockItem->setQty(100)
    ->setIsInStock(true)
    ->setManageStock(true);
$extensionAttributes = $firstProduct->getExtensionAttributes();
$extensionAttributes->setStockItem($firstStockItem);

/** @var ProductRepositoryInterface $productRepository */
$firstProduct = $productRepository->save($firstProduct);

$secondProduct = $objectManager->create(Product::class);
$secondProduct->setTypeId('simple')
    ->setCategoryIds([6])
    ->setId(124)
    ->setAttributeSetId(4)
    ->setName('Second Test Product For TableRate')
    ->setSku('simple-tableRate-2')
    ->setPrice(40)
    ->setTaxClassId(0)
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED);
/** @var StockItemInterface $stockItem */
$secondStockItem = $objectManager->create(StockItemInterface::class);
$secondStockItem->setQty(100)
    ->setIsInStock(true)
    ->setManageStock(true);
$extensionAttributes = $secondProduct->getExtensionAttributes();
$extensionAttributes->setStockItem($secondStockItem);

/** @var ProductRepositoryInterface $productRepository */
$secondProduct = $productRepository->save($secondProduct);

$thirdProduct = $objectManager->create(Product::class);
$thirdProduct->setTypeId('simple')
    ->setCategoryIds([6])
    ->setId(658)
    ->setAttributeSetId(4)
    ->setName('Third Test Product For TableRate')
    ->setSku('simple-tableRate-3')
    ->setPrice(50)
    ->setTaxClassId(0)
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(Visibility::VISIBILITY_BOTH)
    ->setStatus(Status::STATUS_ENABLED);
/** @var StockItemInterface $stockItem */
$thirdStockItem = $objectManager->create(StockItemInterface::class);
$thirdStockItem->setQty(100)
    ->setIsInStock(true)
    ->setManageStock(true);
$extensionAttributes = $thirdProduct->getExtensionAttributes();
$extensionAttributes->setStockItem($thirdStockItem);

/** @var ProductRepositoryInterface $productRepository */
$thirdProduct = $productRepository->save($thirdProduct);

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
    ->setReservedOrderId('tableRate')
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress);
$quote->getPayment()->setMethod('checkmo');
$quote->addProduct($firstProduct);
$quote->addProduct($secondProduct);
$quote->addProduct($thirdProduct);
$quote->setIsMultiShipping(false);
$quoteRepository = $objectManager
    ->get(CartRepositoryInterface::class);
$quoteRepository->save($quote);

/** @var QuoteIdMask $quoteIdMask */
$quoteIdMask = $objectManager
    ->create(QuoteIdMaskFactory::class)
    ->create();
$quoteIdMask->setQuoteId($quote->getId());
$quoteIdMask->setDataChanges(true);
$quoteIdMask->save();
