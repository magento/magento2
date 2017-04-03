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
 * Processes AVS codes mapping from PayPal Payflow transaction to
 * electronic merchant systems standard.
 *
 * @see https://developer.paypal.com/docs/classic/payflow/integration-guide/#credit-card-transaction-responses
 * @see http://www.emsecommerce.net/avs_cvv2_response_codes.htm
 */
class AvsEmsCodeMapper implements PaymentVerificationInterface
{
    /**
     * Default code for mismatching mapping.
     *
     * @var string
     */
    private static $unavailableCode = 'U';

    /**
     * List of mapping AVS codes
     *
     * @var array
     */
    private static $avsMap = [
        'YY' => 'Y',
        'NY' => 'A',
        'YN' => 'Z',
        'NN' => 'N'
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
        if ($orderPayment->getMethod() !== Config::METHOD_PAYFLOWPRO) {
            throw new \InvalidArgumentException(
                'The "' . $orderPayment->getMethod() . '" does not supported by Payflow AVS mapper.'
            );
        }

        $additionalInfo = $orderPayment->getAdditionalInformation();
        if (empty($additionalInfo[Info::PAYPAL_AVSADDR]) ||
            empty($additionalInfo[Info::PAYPAL_AVSZIP])
        ) {
            return self::$unavailableCode;
        }

        $streetCode = $additionalInfo[Info::PAYPAL_AVSADDR];
        $zipCode = $additionalInfo[Info::PAYPAL_AVSZIP];
        $key = $zipCode . $streetCode;

        return isset(self::$avsMap[$key]) ? self::$avsMap[$key] : self::$unavailableCode;
    }
}
