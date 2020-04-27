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
 *
 * @deprecated 100.3.5 Starting from Magento 2.3.5 Signifyd core integration is deprecated in favor of
 * official Signifyd integration available on the marketplace
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
