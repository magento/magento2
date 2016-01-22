<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Gateway\Response;

use Braintree\Transaction;
use Magento\BraintreeTwo\Gateway\Config\Config;
use Magento\BraintreeTwo\Gateway\Helper\SubjectReader;
use Magento\BraintreeTwo\Model\Ui\ConfigProvider;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterface;
use Magento\Sales\Api\Data\OrderPaymentExtensionInterfaceFactory;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Api\Data\PaymentTokenInterfaceFactory;
use Magento\Vault\Model\VaultPaymentInterface;

/**
 * Vault Details Handler
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class VaultDetailsHandler implements HandlerInterface
{
    /**
     * @var PaymentTokenInterfaceFactory
     */
    protected $paymentTokenFactory;

    /**
     * @var OrderPaymentExtensionInterfaceFactory
     */
    protected $paymentExtensionFactory;

    /**
     * @var VaultPaymentInterface
     */
    protected $vaultPayment;

    /**
     * @var SubjectReader
     */
    protected $subjectReader;

    /**
     * @var Config
     */
    protected $config;

    /**
     * Constructor
     *
     * @param VaultPaymentInterface $vaultPayment
     * @param PaymentTokenInterfaceFactory $paymentTokenFactory
     * @param OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory
     * @param Config $config
     * @param SubjectReader $subjectReader
     */
    public function __construct(
        VaultPaymentInterface $vaultPayment,
        PaymentTokenInterfaceFactory $paymentTokenFactory,
        OrderPaymentExtensionInterfaceFactory $paymentExtensionFactory,
        Config $config,
        SubjectReader $subjectReader
    ) {
        $this->vaultPayment = $vaultPayment;
        $this->paymentTokenFactory = $paymentTokenFactory;
        $this->paymentExtensionFactory = $paymentExtensionFactory;
        $this->config = $config;
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
        $paymentToken = $this->getVaultPaymentToken($transaction);
        if (null !== $paymentToken) {
            $extensionAttributes = $this->getExtensionAttributes($payment);
            $extensionAttributes->setVaultPaymentToken($paymentToken);
        }
    }

    /**
     * Get vault payment token entity
     *
     * @param \Braintree\Transaction $transaction
     * @return PaymentTokenInterface|null
     */
    protected function getVaultPaymentToken(Transaction $transaction)
    {
        // Check token existing in gateway response
        $token = $transaction->creditCardDetails->token;
        if (empty($token)) {
            return null;
        }

        /** @var PaymentTokenInterface $paymentToken */
        $paymentToken = $this->paymentTokenFactory->create();
        $paymentToken->setGatewayToken($token);

        $paymentToken->setTokenDetails($this->convertDetailsToJSON([
            'type' => $this->getCreditCardType($transaction->creditCardDetails->cardType),
            'maskedCC' => $transaction->creditCardDetails->last4,
            'expirationDate' => $transaction->creditCardDetails->expirationDate
        ]));

        return $paymentToken;
    }

    /**
     * Convert payment token details to JSON
     * @param array $details
     * @return string
     */
    private function convertDetailsToJSON($details)
    {
        $json = \Zend_Json::encode($details);
        return $json ? $json : '{}';
    }

    /**
     * Get type of credit card mapped from Braintree
     *
     * @param string $type
     * @return array
     */
    private function getCreditCardType($type)
    {
        $replaced = str_replace(' ', '-', strtolower($type));
        $mapper = $this->config->getCctypesMapper();

        return $mapper[$replaced];
    }

    /**
     * Get payment extension attributes
     * @param InfoInterface $payment
     * @return OrderPaymentExtensionInterface
     */
    private function getExtensionAttributes(InfoInterface $payment)
    {
        $extensionAttributes = $payment->getExtensionAttributes();
        if (null === $extensionAttributes) {
            $extensionAttributes = $this->paymentExtensionFactory->create();
            $payment->setExtensionAttributes($extensionAttributes);
        }
        return $extensionAttributes;
    }
}
