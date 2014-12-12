<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

require 'customer_two_addresses.php';

/** @var \Magento\Customer\Model\Customer $customer */
$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    'Magento\Customer\Model\Customer'
)->load(
    1
);
$customer->setDefaultBilling(1)->setDefaultShipping(2);
$customer->save();
