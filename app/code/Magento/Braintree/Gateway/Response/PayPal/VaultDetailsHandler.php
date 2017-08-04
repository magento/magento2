<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Response\PayPal;

use Braintree\Transaction;
use Magento\Braintree\Gateway\Helper\SubjectReader;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\Data\PaymentTokenInterfaceFactory;

/**
 * Vault Details Handler
 * @since 2.1.3
 */
class VaultDetailsHandler implements HandlerInterface
{
    /**
     * @var PaymentTokenInterfaceFactory
     * @since 2.1.3
     */
    private $paymentTokenFactory;

    /**
     * @var OrderPaymentExtensionInterfaceFactory
     * @since 2.1.3
     */
    private $paymentExtensionFactory;

    /**
     * @var SubjectReader
     * @since 2.1.3
     */
    private $subjectReader;

    /**
     * @var DateTimeFactory
     * @since 2.1.3
     */
    private $dateTimeFactory;

    /**
     * Constructor
     *
     * @param PaymentTokenInterfaceFactory $paymentTokenFactory
     * @param OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory
     * @param SubjectReader $subjectReader
     * @param DateTimeFactory $dateTimeFactory
     * @since 2.1.3
     */
    public function __construct(
        PaymentTokenInterfaceFactory $paymentTokenFactory,
        OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory,
        SubjectReader $subjectReader,
        DateTimeFactory $dateTimeFactory
    ) {
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->paymentExtensionFactory = $paymentExtensionFactory;
        $this->subjectReader = $subjectReader;
        $this->dateTimeFactory = $dateTimeFactory;
    }

    /**
     * @inheritdoc
     * @since 2.1.3
     */
    public function handle(array $handlingSubject, array $response)
    {
        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $transaction = $this->subjectReader->readTransaction($response);
        $payment = $paymentDO->getPayment();

        // add vault payment token entity to extension attributes
        $paymentToken = $this->getVaultPaymentToken($transaction);
        if ($paymentToken !== null) {
            $extensionAttributes = $this->getExtensionAttributes($payment);
            $extensionAttributes->setVaultPaymentToken($paymentToken);
        }
    }

    /**
     * Get vault payment token entity
     *
     * @param \Braintree\Transaction $transaction
     * @return PaymentTokenInterface|null
     * @since 2.1.3
     */
    private function getVaultPaymentToken(Transaction $transaction)
    {
        // Check token existing in gateway response
        $token = $transaction->paypalDetails->token;
        if (empty($token)) {
            return null;
        }

        /** @var PaymentTokenInterface $paymentToken */
        $paymentToken = $this->paymentTokenFactory->create();
        $paymentToken->setGatewayToken($token);
        $paymentToken->setExpiresAt($this->getExpirationDate());
        $details = json_encode([
            'payerEmail' => $transaction->paypalDetails->payerEmail
        ]);
        $paymentToken->setTokenDetails($details);

        return $paymentToken;
    }

    /**
     * @return string
     * @since 2.1.3
     */
    private function getExpirationDate()
    {
        $expDate = $this->dateTimeFactory->create('now', new \DateTimeZone('UTC'));
        $expDate->add(new \DateInterval('P1Y'));
        return $expDate->format('Y-m-d 00:00:00');
    }

    /**
     * Get payment extension attributes
     * @param InfoInterface $payment
     * @return OrderPaymentExtensionInterface
     * @since 2.1.3
     */
    private function getExtensionAttributes(InfoInterface $payment)
    {
        $extensionAttributes = $payment->getExtensionAttributes();
        if ($extensionAttributes === null) {
            $extensionAttributes = $this->paymentExtensionFactory->create();
            $payment->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }
}
