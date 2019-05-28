<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

use Magento\OfflinePayments\Model\Checkmo;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\Data\PaymentInterfaceFactory;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;
use Magento\TestFramework\Helper\Bootstrap;

/** @var QuoteFactory $quoteFactory */
$quoteFactory = Bootstrap::getObjectManager()->get(QuoteFactory::class);
/** @var QuoteResource $quoteResource */
$quoteResource = Bootstrap::getObjectManager()->get(QuoteResource::class);
/** @var PaymentInterfaceFactory $paymentFactory */
$paymentFactory = Bootstrap::getObjectManager()->get(PaymentInterfaceFactory::class);
/** @var PaymentMethodManagementInterface $paymentMethodManagement */
$paymentMethodManagement = Bootstrap::getObjectManager()->get(PaymentMethodManagementInterface::class);

$quote = $quoteFactory->create();
$quoteResource->load($quote, 'test_quote', 'reserved_order_id');

$payment = $paymentFactory->create([
    'data' => [
        PaymentInterface::KEY_METHOD => Checkmo::PAYMENT_METHOD_CHECKMO_CODE,
    ]
]);
$paymentMethodManagement->set($quote->getId(), $payment);
