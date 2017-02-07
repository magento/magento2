<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Payment\Api\PaymentVerificationInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Default implementation of payment verification interface.
 * The default code value can be configured via DI.
 */
class DefaultPaymentVerification implements PaymentVerificationInterface
{
    /**
     * @var string
     */
    private $default;

    /**
     * @param string $default
     */
    public function __construct($default = '')
    {
        $this->default = $default;
    }

    /**
     * @inheritdoc
     */
    public function getCode(OrderPaymentInterface $orderPayment)
    {
        return $this->default;
    }
}
