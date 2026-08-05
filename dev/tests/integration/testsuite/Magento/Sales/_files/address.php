<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
/** @var $address \Magento\Sales\Model\Order\Address */
$address = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Sales\Model\Order\Address::class
);
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
