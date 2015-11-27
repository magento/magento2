<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\BraintreeTwo\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\BraintreeTwo\Model\Adapter\BraintreeConfiguration;
use Magento\BraintreeTwo\Model\Adapter\BraintreeClientToken;
use Magento\BraintreeTwo\Model\Adminhtml\Source\Environment;

/**
 * Class Config
 * @package Magento\BraintreeTwo\Gateway\Config
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{
    const KEY_ENVIRONMENT = 'environment';
    const KEY_ACTIVE = 'active';
    const KEY_MERCHANT_ID = 'merchant_id';
    const KEY_MERCHANT_ACCOUNT_ID = 'merchant_account_id';
    const KEY_PUBLIC_KEY = 'public_key';
    const KEY_PRIVATE_KEY = 'private_key';
    const KEY_COUNTRY_CREDIT_CARD = 'countrycreditcard';
    const KEY_CC_TYPES = 'cctypes';
    const KEY_CC_TYPES_BRAINTREE_MAPPER = 'cctypes_braintree_mapper';
    const KEY_SDK_URL = 'sdk_url';
    const KEY_USE_CVV = 'useccv';

    /**
     * @var string
     */
    private $clientToken = '';

    /**
     * @var BraintreeConfiguration
     */
    private $braintreeConfiguration;

    /**
     * @var BraintreeClientToken
     */
    private $braintreeClientToken;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param BraintreeConfiguration $braintreeConfiguration
     * @param BraintreeClientToken $braintreeClientToken
     * @param string $methodCode
     * @param string $pathPattern
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        BraintreeConfiguration $braintreeConfiguration,
        BraintreeClientToken $braintreeClientToken,
        $methodCode = '',
        $pathPattern = self::DEFAULT_PATH_PATTERN
    ) {
        parent::__construct(
            $scopeConfig,
            $methodCode,
            $pathPattern
        );

        $this->braintreeConfiguration = $braintreeConfiguration;
        $this->braintreeClientToken = $braintreeClientToken;

        if ($this->getValue(self::KEY_ACTIVE)) {
            $this->initCredentials();
        }
    }

    /**
     * Initializes credentials.
     *
     * @return void
     */
    public function initCredentials()
    {
        if ($this->getValue(self::KEY_ENVIRONMENT) == Environment::ENVIRONMENT_PRODUCTION) {
            $this->braintreeConfiguration->environment(Environment::ENVIRONMENT_PRODUCTION);
        } else {
            $this->braintreeConfiguration->environment(Environment::ENVIRONMENT_SANDBOX);
        }
        $this->braintreeConfiguration->merchantId($this->getValue(self::KEY_MERCHANT_ID));
        $this->braintreeConfiguration->publicKey($this->getValue(self::KEY_PUBLIC_KEY));
        $this->braintreeConfiguration->privateKey($this->getValue(self::KEY_PRIVATE_KEY));
    }

    /**
     * Generate a new client token if necessary
     *
     * @return string
     */
    public function getClientToken()
    {
        if (empty($this->clientToken)) {
            $this->clientToken = $this->braintreeClientToken->generate();
        }

        return $this->clientToken;
    }

    /**
     * Return the country specific card type config
     *
     * @return array
     */
    public function getCountrySpecificCardTypeConfig()
    {
        $countriesCardTypes = unserialize($this->getValue(self::KEY_COUNTRY_CREDIT_CARD));

        return is_array($countriesCardTypes) ? $countriesCardTypes : [];
    }

    /**
     * Retrieve available credit card types
     *
     * @return array
     */
    public function getCcAvailableCardTypes()
    {
        $ccTypes = $this->getValue(self::KEY_CC_TYPES);

        return !empty($ccTypes) ? explode(',', $ccTypes) : [];
    }

    /**
     * Retrieve mapper between Magento and Braintree card types
     *
     * @return array
     */
    public function getCctypesMapper()
    {
        $result = json_decode(
            $this->getValue(self::KEY_CC_TYPES_BRAINTREE_MAPPER),
            true
        );

        return is_array($result) ? $result : [];
    }

    /**
     * Get list of card types available for country
     * @param string $country
     * @return array
     */
    public function getCountryAvailableCardTypes($country)
    {
        $types = $this->getCountrySpecificCardTypeConfig();
        return (!empty($types[$country])) ? $types[$country] : [];
    }

    /**
     * Check if cvv field is enabled
     * @return boolean
     */
    public function useCvv()
    {
        return (bool) $this->getValue(self::KEY_USE_CVV);
    }
}
