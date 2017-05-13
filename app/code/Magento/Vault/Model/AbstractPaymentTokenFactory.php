<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\Data\PaymentTokenInterfaceFactory;

/**
 * Class AbstractPaymentTokenFactory
 * @deprecated
 * @see PaymentTokenFactoryInterface
 */
abstract class AbstractPaymentTokenFactory implements PaymentTokenInterfaceFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var PaymentTokenFactoryInterface
     */
    private $paymentTokenFactory;

    /**
     * AccountPaymentTokenFactory constructor.
     * @param ObjectManagerInterface $objectManager
     * @param PaymentTokenFactoryInterface $paymentTokenFactory
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        PaymentTokenFactoryInterface $paymentTokenFactory = null
    ) {
        $this->objectManager = $objectManager;
        $this->paymentTokenFactory = $paymentTokenFactory ?: $this->objectManager->get(
            PaymentTokenFactoryInterface::class
        );
    }

    /**
     * Create payment token entity
     * @return PaymentTokenInterface
     */
    public function create()
    {
        return $this->paymentTokenFactory->create($this->getType());
    }

    /**
     * @return string
     */
    abstract public function getType();
}
