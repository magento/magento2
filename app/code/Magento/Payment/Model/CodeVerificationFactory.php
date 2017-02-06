<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Payment\Api\CodeVerificationInterface;
use Magento\Payment\Api\Data\CodeVerificationInterfaceFactory;
use Magento\Payment\Gateway\Config\Config;
use Magento\Sales\Api\Data\OrderPaymentInterface;

/**
 * Implementation of code verification interface factory.
 * Creates verification service for provided payment method, or \Magento\Payment\Model\NullCodeVerification::class -
 * if payment method does not support AVS, CVV verifications.
 */
class CodeVerificationFactory implements CodeVerificationInterfaceFactory
{
    /**
     * @var Config
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
    public function __construct(ObjectManagerInterface $objectManager, Config $config)
    {
        $this->config = $config;
        $this->objectManager = $objectManager;
    }

    /**
     * @inheritdoc
     */
    public function create(OrderPaymentInterface $orderPayment)
    {
        $this->config->setMethodCode($orderPayment->getMethod());
        $verificationClass = $this->config->getValue('code_verification');
        if ($verificationClass === null) {
            return $this->objectManager->get(CodeVerificationInterface::class);
        }
        return $this->objectManager->create($verificationClass, [
            'orderPayment' => $orderPayment
        ]);
    }
}
