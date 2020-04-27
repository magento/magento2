<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Payment\Api\PaymentVerificationInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Framework\Exception\ConfigurationMismatchException;

/**
 * Creates verification service for provided payment method, or PaymentVerificationInterface::class
 * if payment method does not support AVS, CVV verifications.
 *
 * @deprecated 100.3.5 Starting from Magento 2.3.5 Signifyd core integration is deprecated in favor of
 * official Signifyd integration available on the marketplace
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
     * @var PaymentVerificationInterface
     */
    private $avsDefaultAdapter;

    /**
     * @var PaymentVerificationInterface
     */
    private $cvvDefaultAdapter;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param ConfigInterface|Config $config
     * @param PaymentVerificationInterface $avsDefaultAdapter
     * @param PaymentVerificationInterface $cvvDefaultAdapter
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        ConfigInterface $config,
        PaymentVerificationInterface $avsDefaultAdapter,
        PaymentVerificationInterface $cvvDefaultAdapter
    ) {
        $this->config = $config;
        $this->objectManager = $objectManager;
        $this->avsDefaultAdapter = $avsDefaultAdapter;
        $this->cvvDefaultAdapter = $cvvDefaultAdapter;
    }

    /**
     * Creates instance of CVV code verification.
     * Exception will be thrown if CVV mapper does not implement PaymentVerificationInterface.
     *
     * @param string $paymentCode
     * @return PaymentVerificationInterface
     * @throws ConfigurationMismatchException
     */
    public function createPaymentCvv($paymentCode)
    {
        return $this->create($this->cvvDefaultAdapter, $paymentCode, 'cvv_ems_adapter');
    }

    /**
     * Creates instance of AVS code verification.
     * Exception will be thrown if AVS mapper does not implement PaymentVerificationInterface.
     *
     * @param string $paymentCode
     * @return PaymentVerificationInterface
     * @throws ConfigurationMismatchException
     */
    public function createPaymentAvs($paymentCode)
    {
        return $this->create($this->avsDefaultAdapter, $paymentCode, 'avs_ems_adapter');
    }

    /**
     * Creates instance of PaymentVerificationInterface.
     * Default implementation will be returned if payment method does not implement PaymentVerificationInterface.
     *
     * @param PaymentVerificationInterface $defaultAdapter
     * @param string $paymentCode
     * @param string $configKey
     * @return PaymentVerificationInterface
     * @throws ConfigurationMismatchException If payment verification instance
     * does not implement PaymentVerificationInterface.
     */
    private function create(PaymentVerificationInterface $defaultAdapter, $paymentCode, $configKey)
    {
        $this->config->setMethodCode($paymentCode);
        $verificationClass = $this->config->getValue($configKey);
        if ($verificationClass === null) {
            return $defaultAdapter;
        }
        $mapper = $this->objectManager->create($verificationClass);
        if (!$mapper instanceof PaymentVerificationInterface) {
            throw new ConfigurationMismatchException(
                __('%1 must implement %2', $verificationClass, PaymentVerificationInterface::class)
            );
        }
        return $mapper;
    }
}
