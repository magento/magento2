<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Vault\Plugin;

use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Vault\Api\PaymentTokenManagementInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionFactory;
use Magento\Vault\Api\Data\PaymentTokenInterface;

/**
 * Plugin for loading vault payment extension attribute to order/payment entity
 * @since 2.1.0
 */
class PaymentVaultAttributesLoad
{
    /**
     * @var OrderPaymentExtensionFactory
     * @since 2.1.0
     */
    protected $paymentExtensionFactory;

    /**
     * @var PaymentTokenManagementInterface
     * @since 2.1.0
     */
    protected $paymentTokenManagement;

    /**
     * @param OrderPaymentExtensionFactory $paymentExtensionFactory
     * @param PaymentTokenManagement|PaymentTokenManagementInterface $paymentTokenManagement
     * @since 2.1.0
     */
    public function __construct(
        OrderPaymentExtensionFactory $paymentExtensionFactory,
        PaymentTokenManagementInterface $paymentTokenManagement
    ) {
        $this->paymentExtensionFactory = $paymentExtensionFactory;
        $this->paymentTokenManagement = $paymentTokenManagement;
    }

    /**
     * Load vault payment extension attribute to order/payment entity
     *
     * @param OrderPaymentInterface $payment
     * @param OrderPaymentExtensionInterface|null $paymentExtension
     * @return OrderPaymentExtensionInterface
     * @since 2.2.0
     */
    public function afterGetExtensionAttributes(
        OrderPaymentInterface $payment,
        OrderPaymentExtensionInterface $paymentExtension = null
    ) {
        if ($paymentExtension === null) {
            $paymentExtension = $this->paymentExtensionFactory->create();
        }

        $paymentToken = $paymentExtension->getVaultPaymentToken();
        if ($paymentToken === null) {
            $paymentToken = $this->paymentTokenManagement->getByPaymentId($payment->getEntityId());
            if ($paymentToken instanceof PaymentTokenInterface) {
                $paymentExtension->setVaultPaymentToken($paymentToken);
            }
            $payment->setExtensionAttributes($paymentExtension);
        }

        return $paymentExtension;
    }
}
