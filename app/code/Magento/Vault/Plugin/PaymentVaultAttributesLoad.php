<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Vault\Plugin;

use Magento\Vault\Model\PaymentTokenManagement;

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
     * @var PaymentTokenManagement
     */
    protected $paymentTokenManagement;

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentExtensionFactory $paymentExtensionFactory
     * @param PaymentTokenManagement $paymentTokenManagement
     */
    public function __construct(
        \Magento\Sales\Api\Data\OrderPaymentExtensionFactory $paymentExtensionFactory,
        PaymentTokenManagement $paymentTokenManagement
    ) {
        $this->paymentExtensionFactory = $paymentExtensionFactory;
        $this->paymentTokenManagement = $paymentTokenManagement;
    }

    /**
     * Load vault payment extension attribute to order/payment entity
     *
     * @param \Magento\Sales\Model\Order\Payment $subject
     * @param \Closure $proceed
     * @return \Magento\Sales\Model\Order\Payment
     */
    public function aroundGetExtensionAttributes(
        \Magento\Sales\Model\Order\Payment $subject,
        \Closure $proceed
    ) {
        // Call native getExtensionAttributes () and get OrderPaymentExtensionInterface
        /** @var \Magento\Sales\Api\Data\OrderPaymentExtensionInterface $paymentExtension */
        $paymentExtension = $proceed();

        if ($paymentExtension === null) {
            $paymentExtension = $this->paymentExtensionFactory->create();
        }

        $paymentToken = $paymentExtension->getVaultPaymentToken();
        if ($paymentToken === null) {
            $paymentToken = $this->paymentTokenManagement->getByPaymentId($subject->getEntityId());
            $paymentExtension->setVaultPaymentToken($paymentToken);
            $subject->setExtensionAttributes($paymentExtension);
        }

        return $paymentExtension;
    }
}
