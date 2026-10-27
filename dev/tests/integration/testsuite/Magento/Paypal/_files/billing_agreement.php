<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
/** @var \Magento\Paypal\Model\Billing\Agreement $billingAgreement */
$billingAgreement = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
    \Magento\Paypal\Model\Billing\Agreement::class
)->setAgreementLabel(
    'TEST'
)->setCustomerId(
    1
)->setMethodCode(
    'paypal_express'
)->setReferenceId(
    'REF-ID-TEST-678'
)->setStatus(
    Magento\Paypal\Model\Billing\Agreement::STATUS_ACTIVE
)->setStoreId(
    1
)->save();
