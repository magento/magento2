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
 * Use \Magento\Braintree\Model\Adapter\BraintreeAdapterFactory to create new instance of adapter.
 * @codeCoverageIgnore
 */
class BraintreeAdapter
{
    /**
     * @var Config
     */
    private $config;

    /**
     * All arguments have `null` values to provide backward compatibility. This class MUST be initialized only
     * via BraintreeAdapterFactory.
     *
     * @param Config|null $config
     * @param string|null $merchantId
     * @param string|null $publicKey
     * @param string|null $privateKey
     * @param string|null $environment
     */
    public function __construct(
        Config $config = null,
        $merchantId = null,
        $publicKey = null,
        $privateKey = null,
        $environment = null
    ) {
        $this->config = $config;
        $this->merchantId($merchantId);
        $this->publicKey($publicKey);
        $this->privateKey($privateKey);

        if ($environment == Environment::ENVIRONMENT_PRODUCTION) {
            $this->environment(Environment::ENVIRONMENT_PRODUCTION);
        } else {
            $this->environment(Environment::ENVIRONMENT_SANDBOX);
        }
    }

    /**
     * Initializes credentials.
     *
     * @return void
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
     */
    public function environment($value = null)
    {
        return Configuration::environment($value);
    }

    /**
     * @param string|null $value
     * @return mixed
     */
    public function merchantId($value = null)
    {
        return Configuration::merchantId($value);
    }

    /**
     * @param string|null $value
     * @return mixed
     */
    public function publicKey($value = null)
    {
        return Configuration::publicKey($value);
    }

    /**
     * @param string|null $value
     * @return mixed
     */
    public function privateKey($value = null)
    {
        return Configuration::privateKey($value);
    }

    /**
     * @param array $params
     * @return \Braintree\Result\Successful|\Braintree\Result\Error|null
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
     */
    public function search(array $filters)
    {
        return Transaction::search($filters);
    }

    /**
     * @param string $token
     * @return \Braintree\Result\Successful|\Braintree\Result\Error
     */
    public function createNonce($token)
    {
        return PaymentMethodNonce::create($token);
    }

    /**
     * @param array $attributes
     * @return \Braintree\Result\Successful|\Braintree\Result\Error
     */
    public function sale(array $attributes)
    {
        return Transaction::sale($attributes);
    }

    /**
     * @param string $transactionId
     * @param null|float $amount
     * @return \Braintree\Result\Successful|\Braintree\Result\Error
     */
    public function submitForSettlement($transactionId, $amount = null)
    {
        return Transaction::submitForSettlement($transactionId, $amount);
    }

    /**
     * @param string $transactionId
     * @return \Braintree\Result\Successful|\Braintree\Result\Error
     */
    public function void($transactionId)
    {
        return Transaction::void($transactionId);
    }

    /**
     * @param string $transactionId
     * @param null|float $amount
     * @return \Braintree\Result\Successful|\Braintree\Result\Error
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
     */
    public function cloneTransaction($transactionId, array $attributes)
    {
        return Transaction::cloneTransaction($transactionId, $attributes);
    }
}
