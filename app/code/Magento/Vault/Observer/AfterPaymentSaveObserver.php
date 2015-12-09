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
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @param \Magento\Sales\Api\Data\OrderPaymentExtensionFactory $paymentExtensionFactory
     * @param PaymentTokenManagement $paymentTokenManagement
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     */
    public function __construct(
        \Magento\Sales\Api\Data\OrderPaymentExtensionFactory $paymentExtensionFactory,
        PaymentTokenManagement $paymentTokenManagement,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        $this->paymentExtensionFactory = $paymentExtensionFactory;
        $this->paymentTokenManagement = $paymentTokenManagement;
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
        /** @var PaymentTokenInterface $paymentToken */
        $paymentToken = null;

        $extensionAttributes = $payment->getExtensionAttributes();
        if (null === $extensionAttributes ||
            null === ($paymentToken = $extensionAttributes->getVaultPaymentToken()) ||
            '' == $paymentToken->getGatewayToken()
        ) {
            return $this;
        }

        $paymentToken->setPublicHash($this->getPublicHash($paymentToken));
        $paymentToken->setIsActive(true);

        $this->paymentTokenManagement->saveTokenWithPaymentLink($paymentToken, $payment);
        $extensionAttributes->setVaultPaymentToken($paymentToken);

        return $this;
    }

    /**
     * Get public vault payment hash
     *
     * @param PaymentTokenInterface $paymentToken
     * @return string
     */
    protected function getPublicHash(PaymentTokenInterface $paymentToken)
    {
        $hashKey = $paymentToken->getCustomerId() . $paymentToken->getGatewayToken();
        return $this->encryptor->getHash($hashKey);
    }
}
