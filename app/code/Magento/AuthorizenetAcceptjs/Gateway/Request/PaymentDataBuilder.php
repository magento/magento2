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

            // @TODO integrate the real payment values from accept.js
            $descriptor = $payment->encrypt('abc123');
            $value = $payment->encrypt('321cba');

            $this->passthroughData->setData('opaqueDataDescriptor', $descriptor);
            $this->passthroughData->setData('opaqueDataValue', $value);

            $data['transactionRequest']['payment'] = [
                'creditCard' => [
                    'cardNumber' => '4111111111111111',
                    'expirationDate' => '2019-12',
                    'cardCode' => '123'
                ]
                /*'opaqueData' => [
                    'dataDescriptor' => $descriptor,
                    'dataValue' => $value
                ]*/
            ];
        }

        return $data;
    }
}
