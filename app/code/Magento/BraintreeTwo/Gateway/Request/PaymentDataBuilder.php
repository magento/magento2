<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Gateway\Request;

use Magento\Payment\Gateway\Config\ValueHandlerPoolInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\BraintreeTwo\Gateway\Config\Config;
use Magento\BraintreeTwo\Observer\DataAssignObserver;

/**
 * Class PaymentDataBuilder
 */
class PaymentDataBuilder implements BuilderInterface
{
    /**
     * The billing amount of the request. This value must be greater than 0,
     * and must match the currency format of the merchant account.
     */
    const AMOUNT = 'amount';

    /**
     * One-time-use token that references a payment method provided by your customer,
     * such as a credit card or PayPal account.
     *
     * The nonce serves as proof that the user has authorized payment (e.g. credit card number or PayPal details).
     * This should be sent to your server and used with any of Braintree's server-side client libraries
     * that accept new or saved payment details.
     * This can be passed instead of a payment_method_token parameter.
     */
    const PAYMENT_METHOD_NONCE = 'paymentMethodNonce';

    /**
     * The merchant account ID used to create a transaction.
     * Currency is also determined by merchant account ID.
     * If no merchant account ID is specified, Braintree will use your default merchant account.
     */
    const MERCHANT_ACCOUNT_ID = 'merchantAccountId';

    /**
     * Additional options in request to gateway
     */
    const OPTIONS = 'options';

    /**
     * The option that determines whether the payment method
     * associated with the successful transaction should be stored in the Vault.
     */
    const STORE_IN_VAULT = 'storeInVault';

    /**
     * The option that determines whether the shipping address information
     * provided with the transaction should be associated with the customer ID specified.
     * When passed, the payment method will always be stored in the Vault.
     */
    const STORE_IN_VAULT_ON_SUCCESS = 'storeInVaultOnSuccess';

    /**
     * "Is active" vault module config option name
     */
    const CONFIG_PAYMENT_VAULT_ACTIVE = 'active';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ValueHandlerPoolInterface
     */
    private $vaultPaymentValueHandlerPool;

    /**
     * @param Config $config
     * @param ValueHandlerPoolInterface $vaultPaymentValueHandlerPool
     */
    public function __construct(
        Config $config,
        ValueHandlerPoolInterface $vaultPaymentValueHandlerPool
    ) {
        $this->config = $config;
        $this->vaultPaymentValueHandlerPool = $vaultPaymentValueHandlerPool;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $paymentDO = SubjectReader::readPayment($buildSubject);

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $paymentDO->getPayment();

        $result = [
            self::AMOUNT => sprintf('%.2F', SubjectReader::readAmount($buildSubject)),
            self::PAYMENT_METHOD_NONCE => $payment->getAdditionalInformation(
                DataAssignObserver::PAYMENT_METHOD_NONCE
            )
        ];

        $merchantAccountId = $this->config->getValue(Config::KEY_MERCHANT_ACCOUNT_ID);
        if (!empty($merchantAccountId)) {
            $result[self::MERCHANT_ACCOUNT_ID] = $merchantAccountId;
        }

        $isActiveVaultModule = $this->getIsVaultModuleActive();
        if ($isActiveVaultModule || true) { // TODO: Remove stub after activation of Vault module
            $result[self::OPTIONS][self::STORE_IN_VAULT_ON_SUCCESS] = true;
        }

        return $result;
    }

    /**
     * Is vault module active
     *
     * @return bool
     */
    private function getIsVaultModuleActive()
    {
        $handler = $this->vaultPaymentValueHandlerPool->get(self::CONFIG_PAYMENT_VAULT_ACTIVE);
        $subject = ['field' => self::CONFIG_PAYMENT_VAULT_ACTIVE];
        return (bool) $handler->handle($subject);
    }
}
