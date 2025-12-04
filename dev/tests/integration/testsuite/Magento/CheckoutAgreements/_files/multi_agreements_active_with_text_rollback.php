<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

use Magento\CheckoutAgreements\Model\Agreement;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement as AgreementResource;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/**
 * @var $agreement         Agreement
 * @var $agreementResource AgreementResource
 */
$agreement = $objectManager->create(Agreement::class);
$agreementResource = $objectManager->create(AgreementResource::class);

$agreementResource->load($agreement, 'First Checkout Agreement (active)', 'name');
if ($agreement->getId()) {
    $agreementResource->delete($agreement);
}

$agreement = $objectManager->create(Agreement::class);
$agreementResource->load($agreement, 'Second Checkout Agreement (active)', 'name');
if ($agreement->getId()) {
    $agreementResource->delete($agreement);
}
