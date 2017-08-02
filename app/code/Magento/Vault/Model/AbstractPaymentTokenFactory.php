<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model;

use Magento\Framework\ObjectManagerInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\Data\PaymentTokenInterfaceFactory;
use Magento\Vault\Api\Data\PaymentTokenFactoryInterface;

/**
 * Class AbstractPaymentTokenFactory
 * @deprecated 2.2.0
 * @see PaymentTokenFactoryInterface
 * @since 2.2.0
 */
abstract class AbstractPaymentTokenFactory implements PaymentTokenInterfaceFactory
{
    /**
     * @var ObjectManagerInterface
     * @since 2.2.0
     */
    private $objectManager;

    /**
     * @var PaymentTokenFactoryInterface
     * @since 2.2.0
     */
    private $paymentTokenFactory;

    /**
     * AccountPaymentTokenFactory constructor.
     * @param ObjectManagerInterface $objectManager
     * @param PaymentTokenFactoryInterface $paymentTokenFactory
     * @since 2.2.0
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        PaymentTokenFactoryInterface $paymentTokenFactory = null
    ) {
        if ($paymentTokenFactory === null) {
            $paymentTokenFactory = $objectManager->get(PaymentTokenFactoryInterface::class);
        }

        $this->objectManager = $objectManager;
        $this->paymentTokenFactory = $paymentTokenFactory;
    }

    /**
     * Create payment token entity
     * @return PaymentTokenInterface
     * @since 2.2.0
     */
    public function create()
    {
        return $this->paymentTokenFactory->create($this->getType());
    }
}
