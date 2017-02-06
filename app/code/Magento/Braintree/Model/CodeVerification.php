<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model;

use Magento\Braintree\Gateway\Response\PaymentDetailsHandler;
use Magento\Payment\Api\CodeVerificationInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Processes AVS and CVV codes mapping from Braintree transaction to
 * electronic merchant systems standard.
 *
 * @see https://developers.braintreepayments.com/reference/response/transaction
 * @see http://www.emsecommerce.net/avs_cvv2_response_codes.htm
 */
class CodeVerification implements CodeVerificationInterface
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
     * @var OrderPaymentInterface
     */
    private $orderPayment;

    /**
     * @param OrderPaymentInterface $orderPayment
     */
    public function __construct(OrderPaymentInterface $orderPayment)
    {
        $this->orderPayment = $orderPayment;
    }

    /**
     * @inheritdoc
     */
    public function getAvsCode()
    {
        $additionalInfo = $this->orderPayment->getAdditionalInformation();
        if (empty($additionalInfo[PaymentDetailsHandler::AVS_POSTAL_RESPONSE_CODE]) ||
            empty($additionalInfo[PaymentDetailsHandler::AVS_STREET_ADDRESS_RESPONSE_CODE])
        ) {
            return null;
        }
        $streetCode = $additionalInfo[PaymentDetailsHandler::AVS_STREET_ADDRESS_RESPONSE_CODE];
        $zipCode = $additionalInfo[PaymentDetailsHandler::AVS_POSTAL_RESPONSE_CODE];
        $key = $zipCode . $streetCode;
        return isset(self::$avsMap[$key]) ? self::$avsMap[$key] : 'U';
    }

    /**
     * @inheritdoc
     */
    public function getCvvCode()
    {
        $additionalInfo = $this->orderPayment->getAdditionalInformation();
        if (empty($additionalInfo[PaymentDetailsHandler::CVV_RESPONSE_CODE])) {
            return null;
        }

        $cvv = $additionalInfo[PaymentDetailsHandler::CVV_RESPONSE_CODE];
        return self::$cvvMap[$cvv];
    }
}
