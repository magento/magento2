<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Gateway\Request;

use Magento\BraintreeTwo\Gateway\Config\Config;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\BraintreeTwo\Observer\DataAssignObserver;
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
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $result = [];

        if (!$this->config->getIsFraudProtection()) {
            return $result;
        }
        $paymentDO = SubjectReader::readPayment($buildSubject);

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $paymentDO->getPayment();

        $result[self::DEVICE_DATA] = $payment->getAdditionalInformation(DataAssignObserver::DEVICE_DATA);

        return $result;
    }
}
