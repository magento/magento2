<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

require __DIR__ . '/two_bundle_products_with_separate_shipping.php';
$addressData = include __DIR__ . '/../../../Magento/Sales/_files/address_data.php';

$billingAddress = $objectManager->create(\Magento\Quote\Model\Quote\Address::class, ['data' => $addressData]);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping')->setShippingMethod('flatrate_flatrate');

/** @var \Magento\Quote\Model\Quote\Payment $payment */
$payment = $objectManager->create(\Magento\Quote\Model\Quote\Payment::class);
$payment->setMethod('checkmo');

/** @var \Magento\Catalog\Model\ProductRepository $productRepository */
$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);

$bundleProduct = $productRepository->get('bundle-product-separate-shipping-1');
$bundleProduct2 = $productRepository->get('bundle-product-separate-shipping-2');
$selectionProducts = [
    $bundleProduct->getId() => [10, 12],
    $bundleProduct2->getId() => [11, 13],
];

/** @var $cart \Magento\Checkout\Model\Cart */
$cart = $objectManager->create(\Magento\Checkout\Model\Cart::class);

foreach ([$bundleProduct, $bundleProduct2] as $product) {

    /** @var $typeInstance \Magento\Bundle\Model\Product\Type */
    $typeInstance = $product->getTypeInstance();
    $typeInstance->setStoreFilter($product->getStoreId(), $product);
    $optionCollection = $typeInstance->getOptionsCollection($product);

    $bundleOptions = [];
    $bundleOptionsQty = [];
    $optionsData = [];
    foreach ($optionCollection as $option) {
        /** @var $option \Magento\Bundle\Model\Option */
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
    ->setCheckoutMethod(\Magento\Quote\Api\CartManagementInterface::METHOD_GUEST)
    ->setPayment($payment);
$cart->save();

/** @var \Magento\Quote\Model\QuoteManagement $quoteManager */
$quoteManager = $objectManager->get(\Magento\Quote\Model\QuoteManagement::class);
$orderId = $quoteManager->placeOrder($cart->getQuote()->getId());

$objectManager->removeSharedInstance(\Magento\Checkout\Model\Session::class);
