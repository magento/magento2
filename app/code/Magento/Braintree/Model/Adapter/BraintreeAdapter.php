<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Adapter;

use Braintree\ClientToken;
use Braintree\Configuration;
use Braintree\CreditCard;
use Braintree\PaymentMethodNonce;
use Braintree\Transaction;
use Magento\Braintree\Gateway\Config\Config;
use Magento\Braintree\Model\Adminhtml\Source\Environment;

/**
 * Class BraintreeAdapter
 * @codeCoverageIgnore
 * @since 2.1.0
 */
class BraintreeAdapter
{

    /**
     * @var Config
     * @since 2.1.0
     */
    private $config;

    /**
     * @param Config $config
     * @since 2.1.0
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->initCredentials();
    }

    /**
     * Initializes credentials.
     *
     * @return void
     * @since 2.1.0
     */
    protected function initCredentials()
    {
        if ($this->config->getValue(Config::KEY_ENVIRONMENT) == Environment::ENVIRONMENT_PRODUCTION) {
            $this->environment(Environment::ENVIRONMENT_PRODUCTION);
        } else {
            $this->environment(Environment::ENVIRONMENT_SANDBOX);
        }
        $this->merchantId($this->config->getValue(Config::KEY_MERCHANT_ID));
        $this->publicKey($this->config->getValue(Config::KEY_PUBLIC_KEY));
        $this->privateKey($this->config->getValue(Config::KEY_PRIVATE_KEY));
    }

    /**
     * @param string|null $value
     * @return mixed
     * @since 2.1.0
     */
    public function environment($value = null)
    {
        return Configuration::environment($value);
    }

    /**
     * @param string|null $value
     * @return mixed
     * @since 2.1.0
     */
    public function merchantId($value = null)
    {
        return Configuration::merchantId($value);
    }

    /**
     * @param string|null $value
     * @return mixed
     * @since 2.1.0
     */
    public function publicKey($value = null)
    {
        return Configuration::publicKey($value);
    }

    /**
     * @param string|null $value
     * @return mixed
     * @since 2.1.0
     */
    public function privateKey($value = null)
    {
        return Configuration::privateKey($value);
    }

    /**
     * @param array $params
     * @return \Braintree\Result\Successful|\Braintree\Result\Error|null
     * @since 2.1.0
     */
    public function generate(array $params = [])
    {
        try {
            return ClientToken::generate($params);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param string $token
     * @return \Braintree\CreditCard|null
     * @since 2.1.0
     */
    public function find($token)
    {
        try {
            return CreditCard::find($token);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @param array $filters
     * @return \Braintree\ResourceCollection
     * @since 2.1.0
     */
    public function search(array $filters)
    {
        return Transaction::search($filters);
    }

    /**
     * @param string $token
     * @return \Braintree\Result\Successful|\Braintree\Result\Error
     * @since 2.1.0
     */
    public function createNonce($token)
    {
        return PaymentMethodNonce::create($token);
    }

    /**
     * @param array $attributes
     * @return \Braintree\Result\Successful|\Braintree\Result\Error
     * @since 2.1.0
     */
    public function sale(array $attributes)
    {
        return Transaction::sale($attributes);
    }

    /**
     * @param string $transactionId
     * @param null|float $amount
     * @return \Braintree\Result\Successful|\Braintree\Result\Error
     * @since 2.1.0
     */
    public function submitForSettlement($transactionId, $amount = null)
    {
        return Transaction::submitForSettlement($transactionId, $amount);
    }

    /**
     * @param string $transactionId
     * @return \Braintree\Result\Successful|\Braintree\Result\Error
     * @since 2.1.0
     */
    public function void($transactionId)
    {
        return Transaction::void($transactionId);
    }

    /**
     * @param string $transactionId
     * @param null|float $amount
     * @return \Braintree\Result\Successful|\Braintree\Result\Error
     * @since 2.1.0
     */
    public function refund($transactionId, $amount = null)
    {
        return Transaction::refund($transactionId, $amount);
    }

    /**
     * Clone original transaction
     * @param string $transactionId
     * @param array $attributes
     * @return mixed
     * @since 2.1.0
     */
    public function cloneTransaction($transactionId, array $attributes)
    {
        return Transaction::cloneTransaction($transactionId, $attributes);
    }
}
