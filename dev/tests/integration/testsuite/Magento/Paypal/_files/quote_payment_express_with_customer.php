<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

require __DIR__ . '/../../Customer/_files/customer.php';
require __DIR__ . '/../../Customer/_files/customer_two_addresses.php';

\Magento\TestFramework\Helper\Bootstrap::getInstance()->loadArea('adminhtml');

\Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get('Magento\Framework\App\Config\MutableScopeConfigInterface')
    ->setValue('carriers/flatrate/active', 1, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
\Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->get('Magento\Framework\App\Config\MutableScopeConfigInterface')
    ->setValue('payment/paypal_express/active', 1, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

/** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
$customerRepository = $objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
$customer = $customerRepository->getById(1);

/** @var $product \Magento\Catalog\Model\Product */
$product = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Catalog\Model\Product');
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

$customerBillingAddress = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create('Magento\Customer\Model\Address');
$customerBillingAddress->load(1);
$billingAddressDataObject = $customerBillingAddress->getDataModel();
$billingAddress = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create('Magento\Quote\Model\Quote\Address');
$billingAddress->importCustomerAddressData($billingAddressDataObject);
$billingAddress->setAddressType('billing');

/** @var \Magento\Customer\Model\Address $customerShippingAddress */
$customerShippingAddress = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create('Magento\Customer\Model\Address');
$customerShippingAddress->load(2);
$shippingAddressDataObject = $customerShippingAddress->getDataModel();
$shippingAddress = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create('Magento\Quote\Model\Quote\Address');
$shippingAddress->importCustomerAddressData($shippingAddressDataObject);
$shippingAddress->setAddressType('shipping');

$shippingAddress->setShippingMethod('flatrate_flatrate');
$shippingAddress->setCollectShippingRates(true);

/** @var $quote \Magento\Quote\Model\Quote */
$quote = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create('Magento\Quote\Model\Quote');
$quote->setCustomerIsGuest(false)
    ->setCustomerId($customer->getId())
    ->setCustomer($customer)
    ->setStoreId(
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Store\Model\StoreManagerInterface')
            ->getStore()->getId()
    )
    ->setReservedOrderId('test02')
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->addProduct($product, 10);
$quote->getShippingAddress()->setShippingMethod('flatrate_flatrate');
$quote->getShippingAddress()->setCollectShippingRates(true);
$quote->getPayment()->setMethod(\Magento\Paypal\Model\Config::METHOD_WPS_EXPRESS);
$quote->collectTotals()->save();

/** @var $service \Magento\Quote\Api\CartManagementInterface */
$service = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create('\Magento\Quote\Api\CartManagementInterface');
$order = $service->submit($quote, ['increment_id' => '100000002']);
