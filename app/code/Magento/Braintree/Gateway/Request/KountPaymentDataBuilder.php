<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Request;

use Magento\Braintree\Gateway\Config\Config;
use Magento\Braintree\Gateway\SubjectReader;
use Magento\Braintree\Observer\DataAssignObserver;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class KountPaymentDataBuilder
 */
class KountPaymentDataBuilder implements BuilderInterface
{
    /**
     * Additional data for Advanced Fraud Tools
     */
    const DEVICE_DATA = 'deviceData';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Constructor
     *
     * @param Config $config
     * @param SubjectReader $subjectReader
     */
    public function __construct(Config $config, SubjectReader $subjectReader)
    {
        $this->config = $config;
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $result = [];
        $paymentDO = $this->subjectReader->readPayment($buildSubject);
        $order = $paymentDO->getOrder();

        if (!$this->config->hasFraudProtection($order->getStoreId())) {
            return $result;
        }

        $payment = $paymentDO->getPayment();
        $data = $payment->getAdditionalInformation();

        if (isset($data[DataAssignObserver::DEVICE_DATA])) {
            $result[self::DEVICE_DATA] = $data[DataAssignObserver::DEVICE_DATA];
        }

        return $result;
    }
}
