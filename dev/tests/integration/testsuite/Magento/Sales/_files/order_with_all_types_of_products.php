<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

require 'default_rollback.php';
require __DIR__ . '/../../../Magento/Catalog/_files/product_simple.php';
require __DIR__ . '/../../../Magento/Bundle/_files/product_with_custom_options.php';
require __DIR__ . '/../../../Magento/Downloadable/_files/product_with_custom_options.php';
require __DIR__ . '/../../../Magento/ConfigurableProduct/_files/product_with_custom_options.php';

function getOptionValue(\Magento\Catalog\Api\Data\ProductCustomOptionInterface $option)
{
    $returnValue = null;
    switch ($option->getType()) {
        case 'field':
            $returnValue = 'Test value';
            break;
        case 'date_time':
            $returnValue = [
                'year' => '2015',
                'month' => '9',
                'day' => '9',
                'hour' => '2',
                'minute' => '2',
                'day_part' => 'am',
                'date_internal' => '',
            ];
            break;
        case 'drop_down':
            $returnValue = '3-1-select';
            break;
        case 'radio':
            $returnValue = '4-1-radio';
            break;
    }
    return $returnValue;
}

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
$simpleOrderItem->setBasePrice($product->getPrice());
$simpleOrderItem->setPrice($product->getPrice());
$simpleOrderItem->setRowTotal($product->getPrice());
$simpleOrderItem->setProductType($product->getTypeId());
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
        $customOption->setOptionValue(getOptionValue($option));
        $options[] = $customOption;
    }
    $extensionAttributes->setCustomOptions($options);
    $productOption->setExtensionAttributes($extensionAttributes);
    $orderItem->setProductOption($productOption);

    $repository->save($orderItem);
}
