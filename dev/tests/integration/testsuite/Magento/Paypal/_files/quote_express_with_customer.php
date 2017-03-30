<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
require __DIR__ . '/../../Customer/_files/customer.php';
require __DIR__ . '/../../Customer/_files/customer_two_addresses.php';

\Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea('adminhtml');

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$objectManager->get(
    \Magento\Framework\App\Config\MutableScopeConfigInterface::class
)->setValue('carriers/flatrate/active', 1, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
$objectManager->get(\Magento\Framework\App\Config\MutableScopeConfigInterface::class)
    ->setValue('payment/paypal_express/active', 1, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

/** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
$customerRepository = $objectManager->create(\Magento\Customer\Api\CustomerRepositoryInterface::class);
$customer = $customerRepository->getById(1);

/** @var $product \Magento\Catalog\Model\Product */
$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product->setTypeId('simple')
    ->setId(1)
    ->setAttributeSetId(4)
    ->setName('Simple Product')
    ->setSku('simple')
    ->setPrice(10)
    ->setStockData([
        'use_config_manage_stock' => 1,
        'qty' => 100,
        'is_qty_decimal' => 0,
        'is_in_stock' => 100,
    ])
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->save();
$product->load(1);

$customerBillingAddress = $objectManager->create(\Magento\Customer\Model\Address::class);
$customerBillingAddress->load(1);
$billingAddressDataObject = $customerBillingAddress->getDataModel();
$billingAddress = $objectManager->create(\Magento\Quote\Model\Quote\Address::class);
$billingAddress->importCustomerAddressData($billingAddressDataObject);
$billingAddress->setAddressType('billing');

/** @var \Magento\Customer\Model\Address $customerShippingAddress */
$customerShippingAddress = $objectManager->create(\Magento\Customer\Model\Address::class);
$customerShippingAddress->load(2);
$shippingAddressDataObject = $customerShippingAddress->getDataModel();
$shippingAddress = $objectManager->create(\Magento\Quote\Model\Quote\Address::class);
$shippingAddress->importCustomerAddressData($shippingAddressDataObject);
$shippingAddress->setAddressType('shipping');

$shippingAddress->setShippingMethod('flatrate_flatrate');
$shippingAddress->setCollectShippingRates(true);

/** @var $quote \Magento\Quote\Model\Quote */
$quote = $objectManager->create(\Magento\Quote\Model\Quote::class);
$quote->setCustomerIsGuest(false)
    ->setCustomerId($customer->getId())
    ->setCustomer($customer)
    ->setStoreId($objectManager->get(\Magento\Store\Model\StoreManagerInterface::class)->getStore()->getId())
    ->setReservedOrderId('test02')
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->addProduct($product, 10);
$quote->getShippingAddress()->setShippingMethod('flatrate_flatrate');
$quote->getShippingAddress()->setCollectShippingRates(true);
$quote->getPayment()->setMethod(\Magento\Paypal\Model\Config::METHOD_WPS_EXPRESS);

/** @var \Magento\Quote\Api\CartRepositoryInterface $quoteRepository */
$quoteRepository = $objectManager->create(\Magento\Quote\Api\CartRepositoryInterface::class);
$quoteRepository->save($quote);
$quote = $quoteRepository->get($quote->getId());
