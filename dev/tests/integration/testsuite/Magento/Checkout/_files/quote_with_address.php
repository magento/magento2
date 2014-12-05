<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require __DIR__ . '/../../Customer/_files/customer.php';
require __DIR__ . '/../../Customer/_files/customer_address.php';
require __DIR__ . '/../../../Magento/Catalog/_files/products.php';

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Sales\Model\Quote\Address $quoteShippingAddress */
$quoteShippingAddress = $objectManager->create('Magento\Sales\Model\Quote\Address');

/** @var \Magento\Customer\Api\AccountManagementInterface $accountManagement */
$accountManagement = $objectManager->create('Magento\Customer\Api\AccountManagementInterface');

/** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
$customerRepository = $objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
$customer = $customerRepository->getById(1);

/** @var \Magento\Customer\Api\AddressRepositoryInterface $addressRepository */
$addressRepository = $objectManager->create('Magento\Customer\Api\AddressRepositoryInterface');
$quoteShippingAddress->importCustomerAddressData($addressRepository->getById(1));


/** @var \Magento\Sales\Model\Quote $quote */
$quote = $objectManager->create('Magento\Sales\Model\Quote');
$quote->setStoreId(
    1
)->setIsActive(
    true
)->setIsMultiShipping(
    false
)->assignCustomerWithAddressChange(
    $customer
)->setShippingAddress(
    $quoteShippingAddress
)->setBillingAddress(
    $quoteShippingAddress
)->setCheckoutMethod(
    'customer'
)->setPasswordHash(
    $accountManagement->getPasswordHash('password')
)->setReservedOrderId(
    'test_order_1'
)->setCustomerEmail(
    'aaa@aaa.com'
)->addProduct(
    $product->load($product->getId()),
    2
);
