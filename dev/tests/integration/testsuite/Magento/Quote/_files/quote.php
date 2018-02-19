<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
use Magento\TestFramework\Helper\Bootstrap;

Bootstrap::getInstance()->loadArea(Magento\Framework\App\Area::AREA_FRONTEND);

$product = Bootstrap::getObjectManager()->create(\Magento\Catalog\Model\Product::class);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId(4)
    ->setName('Simple Product')
    ->setSku('simple')
    ->setPrice(10)
    ->setMetaTitle('meta title')
    ->setMetaKeyword('meta keyword')
    ->setMetaDescription('meta description')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData([
        'use_config_manage_stock' => 1,
        'qty' => 100,
        'is_qty_decimal' => 0,
        'is_in_stock' => 1,
    ]);

/** @var Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = Bootstrap::getObjectManager()->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$product = $productRepository->save($product);

$addressData = include __DIR__ . '/../../Sales/_files/address_data.php';
$billingAddress = Bootstrap::getObjectManager()->create(
    \Magento\Quote\Model\Quote\Address::class,
    ['data' => $addressData]
);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');

/** @var \Magento\Store\Api\Data\StoreInterface $store */
$store = Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get(\Magento\Store\Model\StoreManagerInterface::class)
    ->getStore();

/** @var \Magento\Quote\Model\Shipping $shipping */
$shipping = Bootstrap::getObjectManager()->create(\Magento\Quote\Model\Shipping::class);
$shipping->setAddress($shippingAddress);
/** @var \Magento\Quote\Model\ShippingAssignment $shippingAssignment */
$shippingAssignment = Bootstrap::getObjectManager()->create(\Magento\Quote\Model\ShippingAssignment::class);
$shippingAssignment->setItems([]);
$shippingAssignment->setShipping($shipping);
/** @var \Magento\Quote\Api\Data\CartExtension $extensionAttributes */
$extensionAttributes = Bootstrap::getObjectManager()->create(\Magento\Quote\Api\Data\CartExtension::class);
$extensionAttributes->setShippingAssignments([$shippingAssignment]);

/** @var \Magento\Quote\Model\Quote $quote */
$quote = Bootstrap::getObjectManager()->create(\Magento\Quote\Model\Quote::class);
$quote->setCustomerIsGuest(true)
    ->setStoreId($store->getId())
    ->setReservedOrderId('test01')
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->addProduct($product);
$quote->getPayment()->setMethod('checkmo');
$quote->setIsMultiShipping('1');
$quote->collectTotals();
$quote->setExtensionAttributes($extensionAttributes);
$quote->save();

/** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
$quoteIdMask = Bootstrap::getObjectManager()
    ->create(\Magento\Quote\Model\QuoteIdMaskFactory::class)
    ->create();
$quoteIdMask->setQuoteId($quote->getId());
$quoteIdMask->setDataChanges(true);
$quoteIdMask->save();
