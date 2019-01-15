<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
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
     * @param SubjectReader $subjectReader
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
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

            $data['transactionRequest']['payment'] = [
                'creditCard' => [
                    'cardNumber' => '4111111111111111',
                    'expirationDate' => '2019-12',
                    'cardCode' => '123'
                ]
                /*'opaqueData' => [
                    // @TODO integrate the real payment values from accept.js
                    'dataDescriptor' => '???',
                    'dataValue' => '???'
                ]*/
            ];
        }

        return $data;
    }
}
