<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Model\Config;

use \Braintree_CreditCard;
use \Braintree_PaymentMethod;
use \Braintree_Exception;
use \Braintree_Customer;
use \Braintree_Transaction;
use \Braintree_Configuration;
use \Braintree_Result_Successful;
use \Braintree_ClientToken;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\ResourceModel\Order\Payment\Transaction\CollectionFactory as TransactionCollectionFactory;
use Magento\Sales\Model\Order\Payment\Transaction as PaymentTransaction;
use Magento\Payment\Model\InfoInterface;

class Cc extends \Magento\Braintree\Model\Config
{
    const KEY_USE_VAULT = 'use_vault';
    const KEY_ALLOW_DUPLICATE_CARD = 'duplicate_card';
    const KEY_USE_CVV = 'useccv';
    const KEY_VERIFY_3DSCURE = 'verify_3dsecure';
    const KEY_ADVANCED_FRAUD_PROTECTION = 'fraudprotection';
    const KEY_ADVANCED_FRAUD_JS = 'data_js';
    const KEY_COUNTRY_CREDIT_CARD = 'countrycreditcard';
    const KEY_CC_TYPES = 'cctypes';
    const KEY_ACTIVE = 'active';
    const KEY_FRAUD_PROTECTION = 'fraudprotection';
    const KEY_AUTO_DETECTION = 'enable_cc_detection';

    /**
     * @var string
     */
    protected $methodCode = 'braintree';

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollectionFactory
     */
    protected $countryFactory;

    /**
     * @var \Magento\Braintree\Model\System\Config\Source\Country
     */
    protected $sourceCountry;

    /**
     *
     * @param string $country
     * @param string $ccType
     * @return bool|\Magento\Framework\Phrase
     */
    public function canUseCcTypeForCountry($country, $ccType)
    {
        try {
            $countriesCardTypes = unserialize($this->getConfigData(self::KEY_COUNTRY_CREDIT_CARD));
        } catch (\Exception $e) {
            $countriesCardTypes = false;
        }
        $countryFound = false;
        if ($countriesCardTypes) {
            if (array_key_exists($country, $countriesCardTypes)) {
                if (!in_array($ccType, $countriesCardTypes[$country])) {
                    return __('Credit card type is not allowed for your country.');
                }
                $countryFound = true;
            }
        }
        if (!$countryFound) {
            $availableTypes = explode(',', $this->getConfigData(self::KEY_CC_TYPES));
            if (!in_array($ccType, $availableTypes)) {
                return __('Credit card type is not allowed for this payment method.');
            }
        }
        return false;
    }

    /**
     * If there are any card types for country
     *
     * @param string $country
     * @return array
     */
    public function getApplicableCardTypes($country)
    {
        try {
            $countriesCardTypes = unserialize($this->getConfigData(self::KEY_COUNTRY_CREDIT_CARD));
        } catch (\Exception $e) {
            $countriesCardTypes = false;
        }
        if ($countriesCardTypes && array_key_exists($country, $countriesCardTypes)) {
            $allowedTypes = $countriesCardTypes[$country];
        } else {
            $allowedTypes = explode(',', $this->getConfigData(self::KEY_CC_TYPES));
        }
        if (!is_array($allowedTypes)) {
            $allowedTypes = [$allowedTypes];
        }
        return $allowedTypes;
    }

    /**
     * Return the country specific card type config
     *
     * @return array
     */
    public function getCountrySpecificCardTypeConfig()
    {
        try {
            $countriesCardTypes = unserialize($this->getConfigData(self::KEY_COUNTRY_CREDIT_CARD));
        } catch (\Exception $e) {
            $countriesCardTypes = false;
        }

        return $countriesCardTypes ? $countriesCardTypes : [];
    }

    //@codeCoverageIgnoreStart
    /**
     * @return bool
     */
    public function isActive()
    {
        return (bool)(int)$this->getConfigData(self::KEY_ACTIVE, $this->storeId);
    }

    /**
     * If vault can be used
     *
     * @return bool
     */
    public function useVault()
    {
        return (bool)(int)$this->getConfigData(self::KEY_USE_VAULT, $this->storeId);
    }

    /**
     * If duplicate credit cards are allowed
     *
     * @return bool
     */
    public function allowDuplicateCards()
    {
        return (bool)(int)$this->getConfigData(self::KEY_ALLOW_DUPLICATE_CARD, $this->storeId);
    }

    /**
     * If 3dSecure is enabled
     *
     * @return bool
     */
    public function is3dSecureEnabled()
    {
        return (bool)(int)$this->getConfigData(self::KEY_VERIFY_3DSCURE, $this->storeId);
    }

    /**
     * If Fraud Detection is enabled
     *
     * @return bool
     */
    public function isFraudDetectionEnabled()
    {
        return (bool)(int)$this->getConfigData(self::KEY_ADVANCED_FRAUD_PROTECTION, $this->storeId);
    }

    /**
     * If Credit Card Auto Detection is enabled
     *
     * @return bool
     */
    public function isCcDetectionEnabled()
    {
        return (bool)(int)$this->getConfigData(self::KEY_AUTO_DETECTION, $this->storeId);
    }


    /**
     * Get Advanced Fraud JS
     *
     * @return string
     */
    public function getBraintreeDataJs()
    {
        return $this->getConfigData(self::KEY_ADVANCED_FRAUD_JS);
    }


    /**
     * If to validate cvv
     *
     * @return boolean
     */
    public function useCvv()
    {
        return (bool)(int)$this->getConfigData(self::KEY_USE_CVV, $this->storeId);
    }

    /**
     * @return bool
     */
    public function isFraudProtectionEnabled()
    {
        return (bool)(int)$this->getConfigData(self::KEY_FRAUD_PROTECTION, $this->storeId);
    }
    //@codeCoverageIgnoreEnd
}
