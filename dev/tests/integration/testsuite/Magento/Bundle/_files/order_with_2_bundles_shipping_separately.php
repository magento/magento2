<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\Product\Type as BundleProductType;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Payment;
use Magento\Quote\Model\QuoteManagement;
use Magento\TestFramework\Helper\Bootstrap;

require __DIR__ . '/two_bundle_products_with_separate_shipping.php';
$addressData = include __DIR__ . '/../../../Magento/Sales/_files/address_data.php';

$objectManager = Bootstrap::getObjectManager();
$billingAddress = $objectManager->create(Address::class, ['data' => $addressData]);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)
    ->setAddressType('shipping')
    ->setShippingMethod('flatrate_flatrate');

/** @var Payment $payment */
$payment = $objectManager->create(Payment::class);
$payment->setMethod('checkmo');

/** @var ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(ProductRepositoryInterface::class);

$bundleProduct = $productRepository->get('bundle-product-separate-shipping-1');
$bundleProduct2 = $productRepository->get('bundle-product-separate-shipping-2');
$selectionProducts = [
    $bundleProduct->getId() => [10, 12],
    $bundleProduct2->getId() => [11, 13],
];

/** @var Cart $cart */
$cart = $objectManager->create(Cart::class);

foreach ([$bundleProduct, $bundleProduct2] as $product) {

    /** @var BundleProductType $typeInstance */
    $typeInstance = $product->getTypeInstance();
    $typeInstance->setStoreFilter($product->getStoreId(), $product);
    $optionCollection = $typeInstance->getOptionsCollection($product);

    $bundleOptions = [];
    $bundleOptionsQty = [];
    $optionsData = [];

    /** @var Option $option */
    foreach ($optionCollection as $option) {
        $selectionsCollection = $typeInstance->getSelectionsCollection([$option->getId()], $product);
        $selectionIds = $selectionProducts[$product->getId()];
        $selectionsCollection->addIdFilter($selectionIds);

        foreach ($selectionIds as $productId) {
            $selection = $selectionsCollection->getItemByColumnValue('product_id', $productId);
            if ($selection !== null) {
                $bundleOptions[$option->getId()] = $selection->getSelectionId();
                $optionsData[$option->getId()] = $selection->getProductId();
                $bundleOptionsQty[$option->getId()] = 1;
            }
        }
    }

    $requestInfo = [
        'product' => $product->getId(),
        'bundle_option' => $bundleOptions,
        'bundle_option_qty' => $bundleOptionsQty,
        'qty' => 1,
    ];

    $cart->addProduct($product, $requestInfo);
}

$cart->getQuote()
    ->setReservedOrderId('order_bundle_separately_shipped')
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->setCheckoutMethod(CartManagementInterface::METHOD_GUEST)
    ->setPayment($payment);
$cart->save();

/** @var QuoteManagement $quoteManager */
$quoteManager = $objectManager->get(QuoteManagement::class);
$orderId = $quoteManager->placeOrder($cart->getQuote()->getId());

$objectManager->removeSharedInstance(Session::class);
