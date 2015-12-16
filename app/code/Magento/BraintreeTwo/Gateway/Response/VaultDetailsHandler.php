<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Gateway\Response;

use Braintree\Transaction;
use Magento\BraintreeTwo\Model\Ui\ConfigProvider;
use Magento\BraintreeTwo\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionFactory;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\PaymentTokenFactory;
use Magento\Vault\Model\VaultPaymentInterface;

/**
 * Vault Details Handler
 */
class VaultDetailsHandler implements HandlerInterface
{
    /**
     * @var PaymentTokenFactory
     */
    protected $paymentTokenFactory;

    /**
     * @var OrderPaymentExtensionFactory
     */
    protected $paymentExtensionFactory;

    /**
     * @var VaultPaymentInterface
     */
    protected $vaultPayment;

    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * Constructor
     *
     * @param VaultPaymentInterface $vaultPayment
     * @param PaymentTokenFactory $paymentTokenFactory
     * @param OrderPaymentExtensionFactory $paymentExtensionFactory
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        VaultPaymentInterface $vaultPayment,
        PaymentTokenFactory $paymentTokenFactory,
        OrderPaymentExtensionFactory $paymentExtensionFactory,
        SubjectReader $subjectReader
    ) {
        $this->vaultPayment = $vaultPayment;
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->paymentExtensionFactory = $paymentExtensionFactory;
        $this->subjectReader = $subjectReader;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $handlingSubject, array $response)
    {
        $isActiveVaultModule = $this->vaultPayment->isActiveForPayment(ConfigProvider::CODE);
        if (!$isActiveVaultModule) {
            return;
        }

        $paymentDO = $this->subjectReader->readPayment($handlingSubject);
        $transaction = $this->subjectReader->readTransaction($response);
        $payment = $paymentDO->getPayment();

        // add vault payment token entity to extension attributes
        $paymentToken = $this->getVaultPaymentToken($transaction, $payment);
        if (null !== $paymentToken) {
            $extensionAttributes = $payment->getExtensionAttributes();
            if (null === $extensionAttributes) {
                $extensionAttributes = $this->paymentExtensionFactory->create();
                $payment->setExtensionAttributes($extensionAttributes);
            }
            $extensionAttributes->setVaultPaymentToken($paymentToken);
        }
    }

    /**
     * Get vault payment token entity
     *
     * @param \Braintree\Transaction $transaction
     * @param OrderPaymentInterface $payment
     * @return PaymentTokenInterface|null
     */
    protected function getVaultPaymentToken(Transaction $transaction, OrderPaymentInterface $payment)
    {
        // Check token existing in gateway response
        $token = $transaction->creditCardDetails->token;
        if (empty($token)) {
            return null;
        }

        $order = $payment->getOrder();

        /** @var PaymentTokenInterface $paymentToken */
        $paymentToken = $this->paymentTokenFactory->create();
        $paymentToken->setGatewayToken($token);
        $paymentToken->setCustomerId($order->getCustomerId());
        $paymentToken->setPaymentMethodCode($payment->getMethod());
        $paymentToken->setCreatedAt($order->getCreatedAt());

        $maskedCC = $transaction->creditCardDetails->maskedNumber;
        if (!empty($maskedCC)) {
            if (strlen($maskedCC) > 4) {
                $maskedCC = substr($maskedCC, -4, 4);
            }
            $paymentToken->setTokenDetails($this->convertDetailsToJSON([
                'maskedCC' => $maskedCC,
                'expirationDate' => $transaction->creditCardDetails->expirationDate
            ]));
        }

        return $paymentToken;
    }

    /**
     * Convert payment token details to JSON
     * @param array $details
     * @return string
     */
    protected function convertDetailsToJSON($details)
    {
        $json = \Zend_Json::encode($details);
        return $json ? $json : '{}';
    }
}
