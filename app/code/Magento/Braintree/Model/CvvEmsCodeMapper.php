<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model;

use Magento\Braintree\Gateway\Response\PaymentDetailsHandler;
use Magento\Braintree\Model\Ui\ConfigProvider;
use Magento\Payment\Api\PaymentVerificationInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Processes CVV codes mapping from Braintree transaction to
 * electronic merchant systems standard.
 *
 * @see https://developers.braintreepayments.com/reference/response/transaction
 * @see http://www.emsecommerce.net/avs_cvv2_response_codes.htm
 * @since 2.2.0
 */
class CvvEmsCodeMapper implements PaymentVerificationInterface
{
    /**
     * Default code for mismatch mapping
     *
     * @var string
     * @since 2.2.0
     */
    private static $notProvidedCode = 'P';

    /**
     * List of mapping CVV codes
     *
     * @var array
     * @since 2.2.0
     */
    private static $cvvMap = [
        'M' => 'M',
        'N' => 'N',
        'U' => 'P',
        'I' => 'P',
        'S' => 'S',
        'A' => ''
    ];

    /**
     * Gets payment CVV verification code.
     *
     * @param OrderPaymentInterface $orderPayment
     * @return string
     * @throws \InvalidArgumentException If specified order payment has different payment method code.
     * @since 2.2.0
     */
    public function getCode(OrderPaymentInterface $orderPayment)
    {
        if ($orderPayment->getMethod() !== ConfigProvider::CODE) {
            throw new \InvalidArgumentException(
                'The "' . $orderPayment->getMethod() . '" does not supported by Braintree CVV mapper.'
            );
        }

        $additionalInfo = $orderPayment->getAdditionalInformation();
        if (empty($additionalInfo[PaymentDetailsHandler::CVV_RESPONSE_CODE])) {
            return self::$notProvidedCode;
        }

        $cvv = $additionalInfo[PaymentDetailsHandler::CVV_RESPONSE_CODE];
        return isset(self::$cvvMap[$cvv]) ? self::$cvvMap[$cvv] : self::$notProvidedCode;
    }
}
