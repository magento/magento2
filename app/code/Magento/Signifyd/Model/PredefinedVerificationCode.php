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
class PredefinedVerificationCode implements PaymentVerificationInterface
{
    /**
     * @var string
     */
    private $code;

    /**
     * @param string $code
     */
    public function __construct($code = '')
    {
        $this->code = $code;
    }

    /**
     * @inheritdoc
     */
    public function getCode(OrderPaymentInterface $orderPayment)
    {
        return $this->code;
    }
}
