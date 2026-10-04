<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\InstantPurchase\CustomerData;

use Magento\InstantPurchase\PaymentMethodIntegration\PaymentTokenFormatterInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;

class StubPaymentTokenFormatter implements PaymentTokenFormatterInterface
{
    const VALUE = 'stub payment token formatting result';

    /**
     * @inheritDoc
     */
    public function formatPaymentToken(PaymentTokenInterface $paymentToken): string
    {
        return self::VALUE;
    }
}
