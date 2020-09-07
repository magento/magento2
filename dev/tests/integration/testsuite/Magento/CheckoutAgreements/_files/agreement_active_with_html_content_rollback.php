<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

use Magento\CheckoutAgreements\Model\Agreement;
use Magento\CheckoutAgreements\Model\ResourceModel\Agreement as AgreementResource;
use Magento\TestFramework\Helper\Bootstrap;

$objectManager = Bootstrap::getObjectManager();

/**
 * @var $agreement Agreement
 * @var $agreementResource AgreementResource
 */
$agreement = $objectManager->create(Agreement::class);
$agreementResource = $objectManager->create(AgreementResource::class);

$agreementResource->load($agreement, 'Checkout Agreement (active)', 'name');
if ($agreement->getId()) {
    $agreementResource->delete($agreement);
}
