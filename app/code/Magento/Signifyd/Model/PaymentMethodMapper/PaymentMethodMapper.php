<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model\PaymentMethodMapper;

use Magento\Framework\Config\Data;

/**
 * Load and cache configuration data.
 */
class PaymentMethodMapper
{
    /**
     * @var Data
     */
    private $paymentMethodMapping;

    /**
     * PaymentMapper constructor.
     *
     * @param Data $paymentMapping
     */
    public function __construct(Data $paymentMapping)
    {
        $this->paymentMethodMapping = $paymentMapping;
    }

    /**
     * Gets the Sygnifyd payment method by the order's payment method.
     *
     * @param string $paymentMethod
     * @return string
     */
    public function getSignifydPaymentMethodCode($paymentMethod)
    {
        return $this->paymentMethodMapping->get($paymentMethod, '');
    }
}
