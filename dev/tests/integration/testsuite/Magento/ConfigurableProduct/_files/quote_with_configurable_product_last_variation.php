<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

require __DIR__ . '/configurable_products.php';

/** @var \Magento\Quote\Model\Quote $quote */
$quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Quote\Model\Quote::class);

$product = $productRepository->getById(10);
$product->setStockData(['use_config_manage_stock' => 1, 'qty' => 1, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);
$productRepository->save($product);

$product = $productRepository->getById(20);
$product->setStockData(['use_config_manage_stock' => 1, 'qty' => 0, 'is_qty_decimal' => 0, 'is_in_stock' => 0]);
$productRepository->save($product);

/** @var \Magento\Quote\Model\Quote $quote */
$quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Quote\Model\Quote::class);
$request = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(\Magento\Framework\DataObject::class);

/** @var \Magento\Eav\Model\Config $eavConfig */
$eavConfig = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(\Magento\Eav\Model\Config::class);
/** @var  $attribute */
$attribute = $eavConfig->getAttribute('catalog_product', 'test_configurable');

$request->setData(
    [
        'product_id' => $productRepository->get('configurable')->getId(),
        'selected_configurable_option' => '1',
        'super_attribute' => [
            $attribute->getAttributeId() => $attribute->getOptions()[1]->getValue()
        ],
        'qty' => '1'
    ]
);

$quote->setStoreId(
        1
    )->setIsActive(
        true
    )->setIsMultiShipping(
        false
    )->setReservedOrderId(
        'test_order_with_configurable_product'
    )->setEmail(
        'store@example.com'
    )->addProduct(
        $productRepository->get('configurable'),
        $request
    );

/** @var \Magento\Quote\Model\QuoteRepository $quoteRepository */
$quoteRepository = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Quote\Model\QuoteRepository::class
);
$quote->collectTotals();
$quoteRepository->save($quote);

/** @var \Magento\Checkout\Model\Session $session */
$session = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Checkout\Model\Session::class
);
$session->setQuoteId($quote->getId());
