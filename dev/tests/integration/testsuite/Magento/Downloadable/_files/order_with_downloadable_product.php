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

$billingAddress = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Sales\Model\Order\Address',
    array(
        'data' => array(
            'firstname' => 'guest',
            'lastname' => 'guest',
            'email' => 'customer@example.com',
            'street' => 'street',
            'city' => 'Los Angeles',
            'region' => 'CA',
            'postcode' => '1',
            'country_id' => 'US',
            'telephone' => '1'
        )
    )
);
$billingAddress->setAddressType('billing');

$payment = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Sales\Model\Order\Payment');
$payment->setMethod('checkmo');

$orderItem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Sales\Model\Order\Item');
$orderItem->setProductId(
    1
)->setProductType(
    \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
)->setBasePrice(
    100
)->setQtyOrdered(
    1
);

$order = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Sales\Model\Order');
$order->setCustomerEmail('mail@to.co')
    ->addItem(
    $orderItem
)->setIncrementId(
    '100000001'
)->setCustomerIsGuest(
    true
)->setStoreId(
    1
)->setEmailSent(
    1
)->setBillingAddress(
    $billingAddress
)->setPayment(
    $payment
);
$order->save();
