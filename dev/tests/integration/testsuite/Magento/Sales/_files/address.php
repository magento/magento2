<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
/** @var $address \Magento\Sales\Model\Order\Address */
$address = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Sales\Model\Order\Address');
$address->setRegion(
    'CA'
)->setPostcode(
    '90210'
)->setFirstname(
    'a_unique_firstname'
)->setLastname(
    'lastname'
)->setStreet(
    'street'
)->setCity(
    'Beverly Hills'
)->setEmail(
    'admin@example.com'
)->setTelephone(
    '1111111111'
)->setCountryId(
    'US'
)->setAddressType(
    'shipping'
)->save();
