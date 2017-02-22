<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model;

use Magento\Braintree\Model\Adapter\BraintreeConfiguration;
use Magento\Braintree\Model\Adapter\BraintreeClientToken;
use \Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    const KEY_ENVIRONMENT = 'environment';
    const KEY_MERCHANT_ACCOUNT_ID = 'merchant_account_id';
    const KEY_ALLOW_SPECIFIC = 'allowspecific';
    const KEY_SPECIFIC_COUNTRY = 'specificcountry';
    const KEY_ACTIVE = 'active';
    const KEY_MERCHANT_ID = 'merchant_id';
    const KEY_PUBLIC_KEY = 'public_key';
    const KEY_PRIVATE_KEY = 'private_key';
    const KEY_DEBUG = 'debug';

    /**
     * @var string
     */
    protected $methodCode = 'braintree';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var string
     */
    protected $merchantAccountId = '';

    /**
     * @var string
     */
    protected $clientToken;

    /**
     * @var int
     */
    protected $storeId = null;

    /**
     * @var \Magento\Braintree\Model\System\Config\Source\Country
     */
    protected $sourceCountry;

    /**
     * @var BraintreeConfiguration
     */
    protected $braintreeConfiguration;

    /**
     * @var BraintreeClientToken
     */
    protected $braintreeClientToken;

    /**
     * @var array
     */
    protected $braintreeSharedConfigFields = [
        'environment' => true,
        'merchant_account_id' => true,
        'merchant_id' => false,
        'public_key' => true,
        'private_key' => false,
    ];

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param BraintreeConfiguration $braintreeConfiguration
     * @param BraintreeClientToken $braintreeClientToken
     * @param \Magento\Braintree\Model\System\Config\Source\Country $sourceCountry
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        BraintreeConfiguration $braintreeConfiguration,
        BraintreeClientToken $braintreeClientToken,
        \Magento\Braintree\Model\System\Config\Source\Country $sourceCountry
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->braintreeConfiguration = $braintreeConfiguration;
        $this->braintreeClientToken = $braintreeClientToken;
        $this->sourceCountry = $sourceCountry;
        if ($this->getConfigData(self::KEY_ACTIVE) == 1) {
            $this->initEnvironment(null);
        }
    }

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }

    /**
     * Retrieve information from payment configuration
     *
     * @param string $field
     * @param null|string $storeId
     *
     * @return mixed
     */
    public function getConfigData($field, $storeId = null)
    {
        if ($storeId == null) {
            $storeId = $this->storeId;
        }
        if (array_key_exists($field, $this->braintreeSharedConfigFields)) {
            $code = PaymentMethod::METHOD_CODE;
        } else {
            $code = $this->methodCode;
        }
        $path = 'payment/' . $code . '/' . $field;
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Initializes environment. This function can be called more than once with different storeId
     *
     * @param int $storeId
     * @return $this
     */
    public function initEnvironment($storeId)
    {
        if ($this->getConfigData('environment', $storeId) ==
            \Magento\Braintree\Model\Source\Environment::ENVIRONMENT_PRODUCTION) {
            $this->braintreeConfiguration->environment(
                \Magento\Braintree\Model\Source\Environment::ENVIRONMENT_PRODUCTION
            );
        } else {
            $this->braintreeConfiguration->environment(
                \Magento\Braintree\Model\Source\Environment::ENVIRONMENT_SANDBOX
            );
        }
        $this->braintreeConfiguration->merchantId($this->getConfigData(self::KEY_MERCHANT_ID, $storeId));
        $this->braintreeConfiguration->publicKey($this->getConfigData(self::KEY_PUBLIC_KEY, $storeId));
        $this->braintreeConfiguration->privateKey($this->getConfigData(self::KEY_PRIVATE_KEY, $storeId));
        $this->merchantAccountId = $this->getConfigData(self::KEY_MERCHANT_ACCOUNT_ID, $storeId);
        $this->storeId = $storeId;
        //Need to set clientToken to null after initialization
        $this->clientToken = null;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return (bool)(int)$this->getConfigData(self::KEY_ACTIVE, $this->storeId);
    }

    /**
     * Generate a new client token if necessary
     *
     * @return string
     */
    public function getClientToken()
    {
        if ($this->clientToken == null) {
            $params = [];

            if (!empty($this->getMerchantAccountId())) {
                $params['merchantAccountId'] = $this->getMerchantAccountId();
            }

            $this->clientToken = $this->braintreeClientToken->generate($params);
        }
        return $this->clientToken;
    }

    /**
     * To check billing country is allowed for the payment method
     *
     * @param string $country
     * @return bool
     */
    public function canUseForCountry($country)
    {
        /*
        for specific country, the flag will set up as 1
        */
        if ($this->getConfigData(self::KEY_ALLOW_SPECIFIC) == 1) {
            $availableCountries = explode(',', $this->getConfigData(self::KEY_SPECIFIC_COUNTRY));
            if (!in_array($country, $availableCountries)) {
                return false;
            }
        } elseif ($this->sourceCountry->isCountryRestricted($country)) {
            return false;
        }
        return true;
    }

    /**
     * @return string
     */
    public function getMerchantAccountId()
    {
        return $this->merchantAccountId;
    }

    /**
     * @return bool
     */
    public function isDebugEnabled()
    {
        return (bool)(int)$this->getConfigData(self::KEY_DEBUG);
    }
}
