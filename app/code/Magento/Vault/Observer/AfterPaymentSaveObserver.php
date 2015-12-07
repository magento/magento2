<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Vault\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order\Payment;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\PaymentTokenFactory;
use Magento\Vault\Model\PaymentTokenManagement;

/**
 * Order payment after save observer for storing payment vault record in db
 */
class AfterPaymentSaveObserver implements ObserverInterface
{
    const PAYMENT_OBJECT_DATA_KEY = 'payment';
    const TRANSACTION_CC_TOKEN_DATA_KEY = 'token';

    /**
     * @var \Magento\Sales\Api\Data\OrderPaymentExtensionFactory
     */
    protected $paymentExtensionFactory;

    /**
     * @var PaymentTokenManagement
     */
    protected $paymentTokenManagement;

    /**
     * @var PaymentTokenFactory
     */
    protected $paymentTokenFactory;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentExtensionFactory $paymentExtensionFactory
     * @param PaymentTokenManagement $paymentTokenManagement
     * @param PaymentTokenFactory $paymentTokenFactory
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     */
    public function __construct(
        \Magento\Sales\Api\Data\OrderPaymentExtensionFactory $paymentExtensionFactory,
        PaymentTokenManagement $paymentTokenManagement,
        PaymentTokenFactory $paymentTokenFactory,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        $this->paymentExtensionFactory = $paymentExtensionFactory;
        $this->paymentTokenManagement = $paymentTokenManagement;
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->encryptor = $encryptor;
    }

    /**
     * Create payment vault record
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var Payment $payment */
        $payment = $observer->getDataByKey(self::PAYMENT_OBJECT_DATA_KEY);
        $order = $payment->getOrder();
        $gatewayToken = $this->getGatewayCcToken($payment);

        if (!empty($gatewayToken)) {
            $customerId = $order->getCustomerId();

            $paymentToken = $this->paymentTokenManagement->getByGatewayToken($customerId, $gatewayToken);
            if ($paymentToken === null) {
                /** @var PaymentTokenInterface $paymentToken */
                $paymentToken = $this->paymentTokenFactory->create();
                $paymentToken->setCreatedAt($order->getCreatedAt());
                $paymentToken->setCustomerId($customerId);
                $paymentToken->setPaymentMethodCode($payment->getMethod());
                $paymentToken->setGatewayToken($gatewayToken);
                $paymentToken->setPublicHash($this->getFrontendHash($paymentToken));
                $paymentToken->setIsActive(true);
            }

            $this->paymentTokenManagement->saveTokenWithPaymentLink($paymentToken, $payment);

            $extensionAttributes = $payment->getExtensionAttributes();
            if ($extensionAttributes === null) {
                $extensionAttributes = $this->paymentExtensionFactory->create();
                $payment->setExtensionAttributes($extensionAttributes);
            }
            $extensionAttributes->setVaultPaymentToken($paymentToken);
        }

        return $this;
    }

    /**
     * Get gateway token from payment info
     *
     * @param Payment $payment
     * @return string|null
     */
    protected function getGatewayCcToken(Payment $payment)
    {
        $gatewayToken = null;
        $transactionAdditionalInfo = $payment->getTransactionAdditionalInfo();

        if (!empty($transactionAdditionalInfo[self::TRANSACTION_CC_TOKEN_DATA_KEY])) {
            $gatewayToken = $transactionAdditionalInfo[self::TRANSACTION_CC_TOKEN_DATA_KEY];
        }

        return $gatewayToken;
    }

    /**
     * Get frontend vault payment hash
     *
     * @param PaymentTokenInterface $entity
     * @return string
     */
    protected function getFrontendHash(PaymentTokenInterface $entity)
    {
        $hashedString = $entity->getCustomerId() . $entity->getGatewayToken() . time();
        return $this->encryptor->getHash($hashedString);
    }
}
