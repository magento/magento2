<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Config
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
    const KEY_VERIFY_3DSECURE = 'verify_3dsecure';
    const KEY_THRESHOLD_AMOUNT = 'threshold_amount';
    const KEY_VERIFY_ALLOW_SPECIFIC = 'verify_all_countries';
    const KEY_VERIFY_SPECIFIC = 'verify_specific_countries';
    const VALUE_3DSECURE_ALL = 0;
    const CODE_3DSECURE = 'three_d_secure';
    const KEY_KOUNT_MERCHANT_ID = 'kount_id';
    const FRAUD_PROTECTION = 'fraudprotection';

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * Braintree config constructor
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param null|string $methodCode
     * @param string $pathPattern
     * @param Json|null $serializer
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        $methodCode = null,
        $pathPattern = self::DEFAULT_PATH_PATTERN,
        Json $serializer = null
    ) {
        parent::__construct($scopeConfig, $methodCode, $pathPattern);
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(Json::class);
    }

    /**
     * Get list of available dynamic descriptors keys
     * @var array
     */
    private static $dynamicDescriptorKeys = [
        'name', 'phone', 'url'
    ];

    /**
     * Return the country specific card type config
     *
     * @return array
     */
    public function getCountrySpecificCardTypeConfig()
    {
        $countryCardTypes = $this->getValue(self::KEY_COUNTRY_CREDIT_CARD);
        if (!$countryCardTypes) {
            return [];
        }
        $countryCardTypes = $this->serializer->unserialize($countryCardTypes);
        return is_array($countryCardTypes) ? $countryCardTypes : [];
    }

    /**
     * Retrieve available credit card types
     *
     * @return array
     */
    public function getAvailableCardTypes()
    {
        $ccTypes = $this->getValue(self::KEY_CC_TYPES);

        return !empty($ccTypes) ? explode(',', $ccTypes) : [];
    }

    /**
     * Retrieve mapper between Magento and Braintree card types
     *
     * @return array
     */
    public function getCcTypesMapper()
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
    public function isCvvEnabled()
    {
        return (bool) $this->getValue(self::KEY_USE_CVV);
    }

    /**
     * Check if 3d secure verification enabled
     * @return bool
     */
    public function isVerify3DSecure()
    {
        return (bool) $this->getValue(self::KEY_VERIFY_3DSECURE);
    }

    /**
     * Get threshold amount for 3d secure
     * @return float
     */
    public function getThresholdAmount()
    {
        return (double) $this->getValue(self::KEY_THRESHOLD_AMOUNT);
    }

    /**
     * Get list of specific countries for 3d secure
     * @return array
     */
    public function get3DSecureSpecificCountries()
    {
        if ((int) $this->getValue(self::KEY_VERIFY_ALLOW_SPECIFIC) == self::VALUE_3DSECURE_ALL) {
            return [];
        }

        return explode(',', $this->getValue(self::KEY_VERIFY_SPECIFIC));
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->getValue(Config::KEY_ENVIRONMENT);
    }

    /**
     * @return string
     */
    public function getKountMerchantId()
    {
        return $this->getValue(Config::KEY_KOUNT_MERCHANT_ID);
    }

    /**
     * @return string
     */
    public function getMerchantId()
    {
        return $this->getValue(Config::KEY_MERCHANT_ID);
    }

    /**
     * @return string
     */
    public function getSdkUrl()
    {
        return $this->getValue(Config::KEY_SDK_URL);
    }

    /**
     * @return bool
     */
    public function hasFraudProtection()
    {
        return (bool) $this->getValue(Config::FRAUD_PROTECTION);
    }

    /**
     * Get Payment configuration status
     * @return bool
     */
    public function isActive()
    {
        return (bool) $this->getValue(self::KEY_ACTIVE);
    }

    /**
     * Get list of configured dynamic descriptors
     * @return array
     */
    public function getDynamicDescriptors()
    {
        $values = [];
        foreach (self::$dynamicDescriptorKeys as $key) {
            $value = $this->getValue('descriptor_' . $key);
            if (!empty($value)) {
                $values[$key] = $value;
            }
        }
        return $values;
    }

    /**
     * Get Merchant account ID
     *
     * @return string
     */
    public function getMerchantAccountId()
    {
        return $this->getValue(self::KEY_MERCHANT_ACCOUNT_ID);
    }
}
