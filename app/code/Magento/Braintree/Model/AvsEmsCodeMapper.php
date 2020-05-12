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
 * Processes AVS codes mapping from Braintree transaction to
 * electronic merchant systems standard.
 *
 * @see https://developers.braintreepayments.com/reference/response/transaction
 * @see http://www.emsecommerce.net/avs_cvv2_response_codes.htm
 * @deprecated Starting from Magento 2.3.6 Braintree payment method core integration is deprecated
 * in favor of official payment integration available on the marketplace
 */
class AvsEmsCodeMapper implements PaymentVerificationInterface
{
    /**
     * Default code for mismatching mapping.
     *
     * @var string
     */
    private static $unavailableCode = '';

    /**
     * List of mapping AVS codes
     *
     * @var array
     */
    private static $avsMap = [
        'MM' => 'Y',
        'NM' => 'A',
        'MN' => 'Z',
        'NN' => 'N',
        'UU' => 'U',
        'II' => 'U',
        'AA' => 'E'
    ];

    /**
     * Gets payment AVS verification code.
     *
     * @param OrderPaymentInterface $orderPayment
     * @return string
     * @throws \InvalidArgumentException If specified order payment has different payment method code.
     */
    public function getCode(OrderPaymentInterface $orderPayment)
    {
        if ($orderPayment->getMethod() !== ConfigProvider::CODE) {
            throw new \InvalidArgumentException(
                'The "' . $orderPayment->getMethod() . '" does not supported by Braintree AVS mapper.'
            );
        }

        $additionalInfo = $orderPayment->getAdditionalInformation();
        if (empty($additionalInfo[PaymentDetailsHandler::AVS_POSTAL_RESPONSE_CODE]) ||
            empty($additionalInfo[PaymentDetailsHandler::AVS_STREET_ADDRESS_RESPONSE_CODE])
        ) {
            return self::$unavailableCode;
        }

        $streetCode = $additionalInfo[PaymentDetailsHandler::AVS_STREET_ADDRESS_RESPONSE_CODE];
        $zipCode = $additionalInfo[PaymentDetailsHandler::AVS_POSTAL_RESPONSE_CODE];
        $key = $zipCode . $streetCode;
        return isset(self::$avsMap[$key]) ? self::$avsMap[$key] : self::$unavailableCode;
    }
}
