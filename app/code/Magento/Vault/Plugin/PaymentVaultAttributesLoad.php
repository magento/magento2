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
 */
class PaymentVaultAttributesLoad
{
    /**
     * @var OrderPaymentExtensionFactory
     */
    protected $paymentExtensionFactory;

    /**
     * @var PaymentTokenManagementInterface
     */
    protected $paymentTokenManagement;

    /**
     * @param OrderPaymentExtensionFactory $paymentExtensionFactory
     * @param PaymentTokenManagement|PaymentTokenManagementInterface $paymentTokenManagement
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
