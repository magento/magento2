<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
\Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea('adminhtml');
\Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    \Magento\Framework\App\Config\MutableScopeConfigInterface::class
)->setValue(
    'carriers/flatrate/active',
    1,
    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
);
\Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
    \Magento\Framework\App\Config\MutableScopeConfigInterface::class
)->setValue(
    'payment/paypal_express/active',
    1,
    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
);
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var $product \Magento\Catalog\Model\Product */
$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product->setTypeId('simple')
    ->setId(1)
    ->setAttributeSetId(4)
    ->setName('Simple Product')
    ->setSku('simple')
    ->setPrice(10)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(
        [
            'qty' => 100,
            'is_in_stock' => 1,
        ]
    )->save();
$product->load(1);

$billingData = [
    'firstname' => 'testname',
    'lastname' => 'lastname',
    'company' => '',
    'email' => 'test@com.com',
    'street' => [
        0 => 'test1',
        1 => '',
    ],
    'city' => 'Test',
    'region_id' => '1',
    'region' => '',
    'postcode' => '9001',
    'country_id' => 'US',
    'telephone' => '11111111',
    'fax' => '',
    'confirm_password' => '',
    'save_in_address_book' => '1',
    'use_for_shipping' => '1',
];

$billingAddress = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Quote\Model\Quote\Address::class, ['data' => $billingData]);
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');
$shippingAddress->setShippingMethod('flatrate_flatrate');
$shippingAddress->setCollectShippingRates(true);

/** @var $quote \Magento\Quote\Model\Quote */
$quote = $objectManager->create(\Magento\Quote\Model\Quote::class);
$quote->setCustomerIsGuest(
    true
)->setStoreId(
    $objectManager->get(
        \Magento\Store\Model\StoreManagerInterface::class
    )->getStore()->getId()
)->setReservedOrderId(
    '100000002'
)->setBillingAddress(
    $billingAddress
)->setShippingAddress(
    $shippingAddress
)->addProduct(
    $product,
    10
);
$quote->getShippingAddress()->setShippingMethod('flatrate_flatrate');
$quote->getShippingAddress()->setCollectShippingRates(true);
$quote->getPayment()->setMethod(\Magento\Paypal\Model\Config::METHOD_WPS_EXPRESS);

$quoteRepository = $objectManager->get(\Magento\Quote\Api\CartRepositoryInterface::class);
$quoteRepository->save($quote);
$quote = $quoteRepository->get($quote->getId());
$quote->setCustomerEmail('admin@example.com');
