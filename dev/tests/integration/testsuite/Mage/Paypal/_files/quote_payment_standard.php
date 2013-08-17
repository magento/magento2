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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
Mage::app()->loadArea('adminhtml');
Mage::app()->getStore()->setConfig('carriers/flatrate/active', 1);
/** @var $product Mage_Catalog_Model_Product */
$product = Mage::getModel('Mage_Catalog_Model_Product');
$product->setTypeId('simple')
    ->setId(1)
    ->setAttributeSetId(4)
    ->setName('Simple Product')
    ->setSku('simple')
    ->setPrice(10)
    ->setStockData(array(
    'use_config_manage_stock' => 1,
    'qty' => 100,
    'is_qty_decimal' => 0,
    'is_in_stock' => 100,
))
    ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
    ->setStatus(Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
    ->save();
$product->load(1);

$addressData = array(
    'region' => 'CA',
    'postcode' => '11111',
    'lastname' => 'lastname',
    'firstname' => 'firstname',
    'street' => 'street',
    'city' => 'Los Angeles',
    'email' => 'admin@example.com',
    'telephone' => '11111111',
    'country_id' => 'US',
);

$billingData = array(
    'address_id' => '',
    'firstname' => 'testname',
    'lastname' => 'lastname',
    'company' => '',
    'email' => 'test@com.com',
    'street' =>
    array(
        0 => 'test1',
        1 => '',
    ),
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
);

$billingAddress = Mage::getModel('Mage_Sales_Model_Quote_Address', array('data' => $billingData));
$billingAddress->setAddressType('billing');

$shippingAddress = clone $billingAddress;
$shippingAddress->setId(null)->setAddressType('shipping');
$shippingAddress->setShippingMethod('flatrate_flatrate');
$shippingAddress->setCollectShippingRates(true);

/** @var $quote Mage_Sales_Model_Quote */
$quote = Mage::getModel('Mage_Sales_Model_Quote');
$quote->setCustomerIsGuest(true)
    ->setStoreId(Mage::app()->getStore()->getId())
    ->setReservedOrderId('test01')
    ->setBillingAddress($billingAddress)
    ->setShippingAddress($shippingAddress)
    ->addProduct($product, 10);
$quote->getShippingAddress()->setShippingMethod('flatrate_flatrate');
$quote->getShippingAddress()->setCollectShippingRates(true);
$quote->collectTotals()->save();

$quote->getPayment()->setMethod(Mage_Paypal_Model_Config::METHOD_WPS);

/** @var $service Mage_Sales_Model_Service_Quote */
$service = Mage::getModel('Mage_Sales_Model_Service_Quote', array('quote' => $quote));
$service->setOrderData(array('increment_id' => '100000002'));
$service->submitAll();

$order = $service->getOrder();
$order->save();
