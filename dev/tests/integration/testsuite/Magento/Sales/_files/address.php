<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
