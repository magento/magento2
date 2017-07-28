<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Request\PayPal;

use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Braintree\Observer\DataAssignObserver;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class DeviceDataBuilder
 * @since 2.2.0
 */
class DeviceDataBuilder implements BuilderInterface
{
    /**
     * @var string
     * @since 2.2.0
     */
    private static $deviceDataKey = 'deviceData';

    /**
     * @var SubjectReader
     * @since 2.2.0
     */
    private $subjectReader;

    /**
     * DeviceDataBuilder constructor.
     * @param SubjectReader $subjectReader
     * @since 2.2.0
     */
    public function __construct(SubjectReader $subjectReader)
    {
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     * @since 2.2.0
     */
    public function build(array $buildSubject)
    {
        $result = [];
        $paymentDO = $this->subjectReader->readPayment($buildSubject);

        $payment = $paymentDO->getPayment();
        $data = $payment->getAdditionalInformation();
        if (!empty($data[DataAssignObserver::DEVICE_DATA])) {
            $result[self::$deviceDataKey] = $data[DataAssignObserver::DEVICE_DATA];
        }

        return $result;
    }
}
