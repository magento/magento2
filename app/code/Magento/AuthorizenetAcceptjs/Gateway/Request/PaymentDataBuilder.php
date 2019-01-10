<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Request;

use Magento\Braintree\Gateway\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Payment\Helper\Formatter;

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
    public function build(array $buildSubject)
    {
        //$payment = $this->subjectReader->readPayment($buildSubject);

        return [
            'transactionRequest' => [
                'amount' => $this->formatPrice($this->subjectReader->readAmount($buildSubject)),
                // TODO Get the PO number, tax, shipping costs
                //'poNumber' => $payment->getPayment()->getAdditionalInformation(),
                //'tax' => $payment->getOrder()->to
                //'shipping' => [
                //    'amount' => $payment->getOrder()->g
                //],
                'payment' => [
                    'opaqueData' => [
                        // @TODO integrate the real payment values from accept.js
                        'dataDescriptor' => '???',
                        'dataValue' => '???'
                    ]
                ]
            ]
        ];
    }
}
