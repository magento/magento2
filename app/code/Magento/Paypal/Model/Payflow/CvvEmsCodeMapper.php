<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Paypal\Model\Payflow;

use Magento\Payment\Api\PaymentVerificationInterface;
use Magento\Paypal\Model\Config;
use Magento\Paypal\Model\Info;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Processes CVV codes mapping from PayPal Payflow transaction to electronic merchant systems standard.
 *
 * @see https://developer.paypal.com/docs/classic/payflow/integration-guide/#credit-card-transaction-responses
 * @see http://www.emsecommerce.net/avs_cvv2_response_codes.htm
 */
class CvvEmsCodeMapper implements PaymentVerificationInterface
{
    /**
     * Default code for mismatch mapping
     *
     * @var string
     */
    private static $notProvidedCode = 'P';

    /**
     * List of mapping CVV codes
     *
     * @var array
     */
    private static $cvvMap = [
        'Y' => 'M',
        'N' => 'N'
    ];

    /**
     * Gets payment CVV verification code.
     *
     * @param OrderPaymentInterface $orderPayment
     * @return string
     * @throws \InvalidArgumentException If specified order payment has different payment method code.
     */
    public function getCode(OrderPaymentInterface $orderPayment)
    {
        if ($orderPayment->getMethod() !== Config::METHOD_PAYFLOWPRO) {
            throw new \InvalidArgumentException(
                'The "' . $orderPayment->getMethod() . '" does not supported by Payflow CVV mapper.'
            );
        }

        $additionalInfo = $orderPayment->getAdditionalInformation();
        if (empty($additionalInfo[Info::PAYPAL_CVV2MATCH])) {
            return self::$notProvidedCode;
        }

        $cvv = $additionalInfo[Info::PAYPAL_CVV2MATCH];

        return self::$cvvMap[$cvv] ?? self::$notProvidedCode;
    }
}
