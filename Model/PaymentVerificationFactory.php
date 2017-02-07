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
 * Creates verification service for provided payment method, or PaymentVerificationInterface::class
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
     * @var DefaultPaymentVerification
     */
    private $avsAdapter;

    /**
     * @var DefaultPaymentVerification
     */
    private $cvvAdapter;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ConfigInterface|Config $config
     * @param DefaultPaymentVerification $avsAdapter
     * @param DefaultPaymentVerification $cvvAdapter
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ConfigInterface $config,
        DefaultPaymentVerification $avsAdapter,
        DefaultPaymentVerification $cvvAdapter
    ) {
        $this->config = $config;
        $this->objectManager = $objectManager;
        $this->avsAdapter = $avsAdapter;
        $this->cvvAdapter = $cvvAdapter;
    }

    /**
     * Creates instance of CVV code verification.
     * Exception will be thrown if CVV mapper does not implement PaymentVerificationInterface.
     *
     * @param string $paymentCode
     * @return PaymentVerificationInterface
     * @throws \Exception
     */
    public function createPaymentCvv($paymentCode)
    {
        return $this->create($this->cvvAdapter, $paymentCode, 'cvv_ems_adapter');
    }

    /**
     * Creates instance of AVS code verification.
     * Exception will be thrown if AVS mapper does not implement PaymentVerificationInterface.
     *
     * @param string $paymentCode
     * @return PaymentVerificationInterface
     * @throws \Exception
     */
    public function createPaymentAvs($paymentCode)
    {
        return $this->create($this->avsAdapter, $paymentCode, 'avs_ems_adapter');
    }

    /**
     * Creates instance of PaymentVerificationInterface.
     * Default implementation will be returned if payment method does not implement PaymentVerificationInterface.
     * Exception will be thrown if payment verification instance does not implement PaymentVerificationInterface.
     *
     * @param DefaultPaymentVerification $defaultAdapter
     * @param string $paymentCode
     * @param string $configKey
     * @return PaymentVerificationInterface
     * @throws \Exception
     */
    private function create(DefaultPaymentVerification $defaultAdapter, $paymentCode, $configKey)
    {
        $this->config->setMethodCode($paymentCode);
        $verificationClass = $this->config->getValue($configKey);
        if ($verificationClass === null) {
            return $defaultAdapter;
        }
        $mapper = $this->objectManager->create($verificationClass);
        if (!$mapper instanceof PaymentVerificationInterface) {
            throw new \Exception($verificationClass . ' must implement ' . PaymentVerificationInterface::class);
        }
        return $mapper;
    }
}
