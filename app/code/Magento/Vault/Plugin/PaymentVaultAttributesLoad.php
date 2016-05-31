<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Vault\Plugin;

use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;

/**
 * Plugin for loading vault payment extension attribute to order/payment entity
 */
class PaymentVaultAttributesLoad
{
    /**
     * @var \Magento\Sales\Api\Data\OrderPaymentExtensionFactory
     */
    protected $paymentExtensionFactory;

    /**
     * @var PaymentTokenManagementInterface
     */
    protected $paymentTokenManagement;

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentExtensionFactory $paymentExtensionFactory
     * @param PaymentTokenManagement|PaymentTokenManagementInterface $paymentTokenManagement
     */
    public function __construct(
        \Magento\Sales\Api\Data\OrderPaymentExtensionFactory $paymentExtensionFactory,
        PaymentTokenManagementInterface $paymentTokenManagement
    ) {
        $this->paymentExtensionFactory = $paymentExtensionFactory;
        $this->paymentTokenManagement = $paymentTokenManagement;
    }

    /**
     * Load vault payment extension attribute to order/payment entity
     *
     * @param OrderPaymentInterface $payment
     * @param \Closure $proceed
     * @return OrderPaymentExtensionInterface
     */
    public function aroundGetExtensionAttributes(
        OrderPaymentInterface $payment,
        \Closure $proceed
    ) {
        /** @var OrderPaymentExtensionInterface $paymentExtension */
        $paymentExtension = $proceed();

        if ($paymentExtension === null) {
            $paymentExtension = $this->paymentExtensionFactory->create();
        }

        $paymentToken = $paymentExtension->getVaultPaymentToken();
        if ($paymentToken === null) {
            $paymentToken = $this->paymentTokenManagement->getByPaymentId($payment->getEntityId());
            if ($paymentToken instanceof \Magento\Vault\Api\Data\PaymentTokenInterface) {
                $paymentExtension->setVaultPaymentToken($paymentToken);
            }
            $payment->setExtensionAttributes($paymentExtension);
        }

        return $paymentExtension;
    }
}
