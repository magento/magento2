<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
/** @var $agreement \Magento\CheckoutAgreements\Model\Agreement */
$agreement = $objectManager->create(\Magento\CheckoutAgreements\Model\Agreement::class);
$agreement->load('First Checkout Agreement (active)', 'name');
if ($agreement->getId()) {
    $agreement->delete();
}
$agreement = $objectManager->create(\Magento\CheckoutAgreements\Model\Agreement::class);
$agreement->load('Second Checkout Agreement (active)', 'name');
if ($agreement->getId()) {
    $agreement->delete();
}
