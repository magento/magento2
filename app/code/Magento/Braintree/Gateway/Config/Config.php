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
     * @param int|null $storeId
     * @return array
     */
    public function getCountrySpecificCardTypeConfig($storeId = null)
    {
        $countryCardTypes = $this->getValue(self::KEY_COUNTRY_CREDIT_CARD, $storeId);
        if (!$countryCardTypes) {
            return [];
        }
        $countryCardTypes = $this->serializer->unserialize($countryCardTypes);
        return is_array($countryCardTypes) ? $countryCardTypes : [];
    }

    /**
     * Retrieve available credit card types
     *
     * @param int|null $storeId
     * @return array
     */
    public function getAvailableCardTypes($storeId = null)
    {
        $ccTypes = $this->getValue(self::KEY_CC_TYPES, $storeId);

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
     * Gets list of card types available for country.
     *
     * @param string $country
     * @param int|null $storeId
     * @return array
     */
    public function getCountryAvailableCardTypes($country, $storeId = null)
    {
        $types = $this->getCountrySpecificCardTypeConfig($storeId);

        return (!empty($types[$country])) ? $types[$country] : [];
    }

    /**
     * Checks if cvv field is enabled.
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isCvvEnabled($storeId = null)
    {
        return (bool)$this->getValue(self::KEY_USE_CVV, $storeId);
    }

    /**
     * Checks if 3d secure verification enabled.
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isVerify3DSecure($storeId = null)
    {
        return (bool)$this->getValue(self::KEY_VERIFY_3DSECURE, $storeId);
    }

    /**
     * Gets threshold amount for 3d secure.
     *
     * @param int|null $storeId
     * @return float
     */
    public function getThresholdAmount($storeId = null)
    {
        return (double) $this->getValue(self::KEY_THRESHOLD_AMOUNT, $storeId);
    }

    /**
     * Gets list of specific countries for 3d secure.
     *
     * @param int|null $storeId
     * @return array
     */
    public function get3DSecureSpecificCountries($storeId = null)
    {
        if ((int) $this->getValue(self::KEY_VERIFY_ALLOW_SPECIFIC, $storeId) == self::VALUE_3DSECURE_ALL) {
            return [];
        }

        return explode(',', $this->getValue(self::KEY_VERIFY_SPECIFIC, $storeId));
    }

    /**
     * Gets value of configured environment.
     * Possible values: production or sandbox.
     *
     * @param int|null $storeId
     * @return string
     */
    public function getEnvironment($storeId = null)
    {
        return $this->getValue(Config::KEY_ENVIRONMENT, $storeId);
    }

    /**
     * Gets Kount merchant ID.
     *
     * @param int|null $storeId
     * @return string
     * @internal param null $storeId
     */
    public function getKountMerchantId($storeId = null)
    {
        return $this->getValue(Config::KEY_KOUNT_MERCHANT_ID, $storeId);
    }

    /**
     * Gets merchant ID.
     *
     * @param int|null $storeId
     * @return string
     */
    public function getMerchantId($storeId = null)
    {
        return $this->getValue(Config::KEY_MERCHANT_ID, $storeId);
    }

    /**
     * Gets Merchant account ID.
     *
     * @param int|null $storeId
     * @return string
     */
    public function getMerchantAccountId($storeId = null)
    {
        return $this->getValue(self::KEY_MERCHANT_ACCOUNT_ID, $storeId);
    }

    /**
     * @return string
     */
    public function getSdkUrl()
    {
        return $this->getValue(Config::KEY_SDK_URL);
    }

    /**
     * Checks if fraud protection is enabled.
     *
     * @param int|null $storeId
     * @return bool
     */
    public function hasFraudProtection($storeId = null)
    {
        return (bool)$this->getValue(Config::FRAUD_PROTECTION, $storeId);
    }

    /**
     * Gets Payment configuration status.
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isActive($storeId = null)
    {
        return (bool)$this->getValue(self::KEY_ACTIVE, $storeId);
    }

    /**
     * Gets list of configured dynamic descriptors.
     *
     * @param int|null $storeId
     * @return array
     */
    public function getDynamicDescriptors($storeId = null)
    {
        $values = [];
        foreach (self::$dynamicDescriptorKeys as $key) {
            $value = $this->getValue('descriptor_' . $key, $storeId);
            if (!empty($value)) {
                $values[$key] = $value;
            }
        }
        return $values;
    }
}
