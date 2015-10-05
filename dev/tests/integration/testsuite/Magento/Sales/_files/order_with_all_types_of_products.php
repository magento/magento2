<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

$optionValue = [
    'field' => 'Test value',
    'date_time' => [
        'year' => '2015',
        'month' => '9',
        'day' => '9',
        'hour' => '2',
        'minute' => '2',
        'day_part' => 'am',
        'date_internal' => '',
    ],
    'drop_down' => '3-1-select',
    'radio' => '4-1-radio',
];

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$productRepository = $objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');

$simpleProduct = $productRepository->get('simple');
$configurableProduct = $productRepository->get('configurable');
$bundleProduct = $productRepository->get('bundle-product');
$downloadableProduct = $productRepository->get('downloadable-product');

$addressData = include __DIR__ . '/address_data.php';
$billingAddress = $objectManager->create('Magento\Sales\Model\Order\Address', ['data' => $addressData]);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');

$payment = $objectManager->create('Magento\Sales\Model\Order\Payment');
$payment->setMethod('checkmo');

/** @var \Magento\Sales\Model\Order $order */
$order = $objectManager->create('Magento\Sales\Model\Order');
$order->setIncrementId('100000001')
    ->setState(\Magento\Sales\Model\Order::STATE_PROCESSING)
    ->setStatus($order
    ->getConfig()
    ->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_PROCESSING))
    ->setSubtotal(100)
    ->setBaseSubtotal(100)
    ->setBaseGrandTotal(100)
    ->setCustomerIsGuest(true)
    ->setCustomerEmail('customer@null.com')
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->setStoreId($objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId())
    ->setPayment($payment);

$simpleOrderItem = $objectManager->create('Magento\Sales\Model\Order\Item');
$simpleOrderItem->setProductId($simpleProduct->getId())->setQtyOrdered(2);
$simpleOrderItem->setOrderId($order->getId());
$simpleOrderItem->setBasePrice($simpleProduct->getPrice());
$simpleOrderItem->setPrice($simpleProduct->getPrice());
$simpleOrderItem->setRowTotal($simpleProduct->getPrice());
$simpleOrderItem->setProductType($simpleProduct->getTypeId());
$order->addItem($simpleOrderItem);
$order->save();

/** @var \Magento\Catalog\Model\ProductOption $productOption */
$productOption = $objectManager->create('Magento\Catalog\Model\ProductOptionFactory')->create();
/** @var  \Magento\Catalog\Api\Data\ProductOptionExtensionInterface $extensionAttributes */
$extensionAttributes = $objectManager->create('Magento\Catalog\Api\Data\ProductOptionExtensionFactory')->create();
$customOptionFactory = $objectManager->create('Magento\Catalog\Model\CustomOptions\CustomOptionFactory');

$repository = $objectManager->create('Magento\Sales\Model\Order\ItemRepository');

/** @var Magento\Catalog\Model\Product $product */
foreach ([$simpleProduct, $configurableProduct, $bundleProduct, $downloadableProduct] as $product) {
    /** @var \Magento\Sales\Model\Order\Item $orderItem */
    $orderItem = $objectManager->create('Magento\Sales\Model\Order\Item');
    $orderItem->setProductId($product->getId())->setQtyOrdered(2);
    $orderItem->setOrderId($order->getId());
    $orderItem->setBasePrice($product->getPrice());
    $orderItem->setPrice($product->getPrice());
    $orderItem->setRowTotal($product->getPrice());
    $orderItem->setProductType($product->getTypeId());
    $options = [];
    /** @var \Magento\Catalog\Api\Data\ProductCustomOptionInterface $option */
    foreach ($product->getOptions() as $option) {
        /** @var \Magento\Catalog\Api\Data\CustomOptionInterface $customOption */
        $customOption = $customOptionFactory->create();
        $customOption->setOptionId($option->getId());
        $customOption->setOptionValue($optionValue[$option]);
        $options[] = $customOption;
    }
    $extensionAttributes->setCustomOptions($options);
    $productOption->setExtensionAttributes($extensionAttributes);
    $orderItem->setProductOption($productOption);

    $repository->save($orderItem);
}
