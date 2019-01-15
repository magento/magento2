<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Response;

use Magento\Braintree\Observer\DataAssignObserver;
use Magento\Braintree\Gateway\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Payment Details Handler
 */
class PaymentDetailsHandler implements HandlerInterface
{
    const AVS_POSTAL_RESPONSE_CODE = 'avsPostalCodeResponseCode';

    const AVS_STREET_ADDRESS_RESPONSE_CODE = 'avsStreetAddressResponseCode';

    const CVV_RESPONSE_CODE = 'cvvResponseCode';

    const PROCESSOR_AUTHORIZATION_CODE = 'processorAuthorizationCode';

    const PROCESSOR_RESPONSE_CODE = 'processorResponseCode';

    const PROCESSOR_RESPONSE_TEXT = 'processorResponseText';

    /**
     * List of additional details
     * @var array
     */
    protected $additionalInformationMapping = [
        self::AVS_POSTAL_RESPONSE_CODE,
        self::AVS_STREET_ADDRESS_RESPONSE_CODE,
        self::CVV_RESPONSE_CODE,
        self::PROCESSOR_AUTHORIZATION_CODE,
        self::PROCESSOR_RESPONSE_CODE,
        self::PROCESSOR_RESPONSE_TEXT,
    ];

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Constructor
     *
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        /** @var \Braintree\Transaction $transaction */
        $transaction = $this->subjectReader->readTransaction($response);
        /** @var OrderPaymentInterface $payment */
        $payment = $paymentDO->getPayment();

        $payment->setCcTransId($transaction->id);
        $payment->setLastTransId($transaction->id);

        //remove previously set payment nonce
        $payment->unsAdditionalInformation(DataAssignObserver::PAYMENT_METHOD_NONCE);
        foreach ($this->additionalInformationMapping as $item) {
            if (!isset($transaction->$item)) {
                continue;
            }
            $payment->setAdditionalInformation($item, $transaction->$item);
        }
    }
}
