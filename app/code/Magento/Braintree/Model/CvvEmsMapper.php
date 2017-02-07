<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model;

use Magento\Braintree\Gateway\Response\PaymentDetailsHandler;
use Magento\Payment\Api\PaymentVerificationInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Processes CVV codes mapping from Braintree transaction to
 * electronic merchant systems standard.
 *
 * @see https://developers.braintreepayments.com/reference/response/transaction
 * @see http://www.emsecommerce.net/avs_cvv2_response_codes.htm
 */
class CvvEmsMapper implements PaymentVerificationInterface
{
    /**
     * List of mapping CVV codes
     *
     * @var array
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
     * Returns null if payment does not contain any CVV details.
     *
     * @return string|null
     */
    public function getCode(OrderPaymentInterface $orderPayment)
    {
        $additionalInfo = $orderPayment->getAdditionalInformation();
        if (empty($additionalInfo[PaymentDetailsHandler::CVV_RESPONSE_CODE])) {
            return null;
        }

        $cvv = $additionalInfo[PaymentDetailsHandler::CVV_RESPONSE_CODE];
        return isset(self::$cvvMap[$cvv]) ? self::$cvvMap[$cvv] : null;
    }
}
