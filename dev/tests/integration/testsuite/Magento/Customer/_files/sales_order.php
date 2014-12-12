<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/** @var \Magento\Customer\Model\Customer $customer */
$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Customer\Model\Customer'
)->load(
    1
);

/** @var \Magento\Sales\Model\Order $order */
$order = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Sales\Model\Order'
)->loadByIncrementId(
    '100000001'
);
$order->setCustomerIsGuest(false)->setCustomerId($customer->getId())->setCustomerEmail($customer->getEmail());
$order->save();
