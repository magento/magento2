<?php
/**
 * Fixture for Customer List method.
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

require 'customer.php';

$customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Customer\Model\Customer');
$customer->setWebsiteId(
    1
)->setEntityId(
    2
)->setEntityTypeId(
    1
)->setAttributeSetId(
    0
)->setEmail(
    'customer_two@example.com'
)->setPassword(
    'password'
)->setGroupId(
    1
)->setStoreId(
    1
)->setIsActive(
    1
)->setFirstname(
    'Firstname'
)->setLastname(
    'Lastname'
)->setDefaultBilling(
    1
)->setDefaultShipping(
    1
);
$customer->isObjectNew(true);
$customer->save();
