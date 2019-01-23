<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\AuthorizenetAcceptjs\Model\PassthroughDataObject;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;

/**
 * Adds the basic payment information to the request
 */
class PaymentDataBuilder implements BuilderInterface
{
    use Formatter;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var PassthroughDataObject
     */
    private $passthroughData;

    /**
     * @param SubjectReader $subjectReader
     * @param PassthroughDataObject $passthroughData
     */
    public function __construct(SubjectReader $subjectReader, PassthroughDataObject $passthroughData)
    {
        $this->subjectReader = $subjectReader;
        $this->passthroughData = $passthroughData;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $payment = $paymentDO->getPayment();
        $order = $paymentDO->getOrder();
        $data = [];

        if ($payment instanceof Payment) {
            $data = [
                'transactionRequest' => [
                    'amount' => $this->formatPrice($this->subjectReader->readAmount($buildSubject)),
                ]
            ];

            if ($order instanceof Order) {
                $data['transactionRequest']['shipping'] = [
                    'amount' => $order->getBaseShippingAmount()
                ];
            }

            $dataDescriptor = $payment->getAdditionalInformation('opaqueDataDescriptor');
            $dataValue = $payment->getAdditionalInformation('opaqueDataValue');

            $encDescriptor = $payment->encrypt($dataDescriptor);
            $encValue = $payment->encrypt($dataValue);

            $this->passthroughData->setData('opaqueDataDescriptor', $encDescriptor);
            $this->passthroughData->setData('opaqueDataValue', $encValue);

            $data['transactionRequest']['payment'] = [
                'creditCard' => [
                    'cardNumber' => '4111111111111111',
                    'expirationDate' => '2019-12',
                    'cardCode' => '123'
                ],
                // @TODO integrate the real payment values from accept.js
                /*'opaqueData' => [
                    'dataDescriptor' => $dataDescriptor,
                    'dataValue' => $dataValue
                ]*/
            ];
        }

        return $data;
    }
}
