<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Payment\Api\PaymentVerificationInterface;
use Magento\Payment\Gateway\ConfigInterface;

/**
 * Creates verification service for provided payment method, or \Magento\Payment\Api\PaymentVerificationInterface::class
 * if payment method does not support AVS, CVV verifications.
 */
class PaymentVerificationFactory
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Config $config
     */
    public function __construct(ObjectManagerInterface $objectManager, ConfigInterface $config)
    {
        $this->config = $config;
        $this->objectManager = $objectManager;
    }

    /**
     * @inheritdoc
     */
    public function createPaymentCvv($paymentCode)
    {
        return $this->create($paymentCode, 'cvv_ems_adapter');
    }

    /**
     * @inheritdoc
     */
    public function createPaymentAvs($paymentCode)
    {
        return $this->create($paymentCode, 'avs_ems_adapter');
    }

    /**
     * @inheritdoc
     */
    private function create($paymentCode, $configKey)
    {
        $this->config->setMethodCode($paymentCode);
        $verificationClass = $this->config->getValue($configKey);
        if ($verificationClass === null) {
            return $this->objectManager->get(PaymentVerificationInterface::class);
        }
        return $this->objectManager->create($verificationClass);
    }
}
