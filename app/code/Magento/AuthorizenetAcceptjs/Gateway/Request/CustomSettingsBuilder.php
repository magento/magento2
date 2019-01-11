<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\Config;
use Magento\Braintree\Gateway\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Adds the basic payment information to the request
 */
class CustomerDataBuilder implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param SubjectReader $subjectReader
     * @param Config $config
     */
    public function __construct(SubjectReader $subjectReader, Config $config)
    {
        $this->subjectReader = $subjectReader;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $order = $paymentDO->getOrder();
        $billingAddress = $order->getBillingAddress();
        $result = [
            'transactionRequest' => [
                'customer' => [
                    'id' => $order->getCustomerId(),
                    'email' => $billingAddress->getEmail()
                ]
            ]
        ];

        if ($this->config->shouldEmailCustomer($this->subjectReader->readStoreId($buildSubject))) {
            $result['transactionRequest']['transactionSettings']['setting'] = [
                [
                    'settingName' => 'emailCustomer',
                    'settingValue' => 'true'
                ]
            ];
        }

        return $result;
    }
}
