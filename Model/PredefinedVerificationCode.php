<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Payment\Api\PaymentVerificationInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Default implementation of payment verification interface.
 * The default code value can be configured via DI.
 * @since 2.2.0
 */
class PredefinedVerificationCode implements PaymentVerificationInterface
{
    /**
     * @var string
     * @since 2.2.0
     */
    private $code;

    /**
     * @param string $code
     * @since 2.2.0
     */
    public function __construct($code = '')
    {
        $this->code = $code;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function getCode(OrderPaymentInterface $orderPayment)
    {
        return $this->code;
    }
}
