<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleAnalytics\Helper;

use Magento\Store\Model\Store;
use Magento\Store\Model\ScopeInterface;

/**
 * GoogleAnalytics data helper
 *
 * @api
 * @since 100.0.2
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Config paths for using throughout the code
     */
    const XML_PATH_ACTIVE = 'google/analytics/active';

    const XML_PATH_ACCOUNT_TYPE = 'google/analytics/account_type';

    const XML_PATH_TRACKING_ID = 'google/analytics/tracking_id';

    const XML_PATH_MEASUREMENT_ID = 'google/analytics/measurement_id';

    const XML_PATH_ANONYMIZE = 'google/analytics/anonymize';

    const XML_PATH_ANONYMIZE_DEFAULT_YES = 'google/analytics/anonymize_default_yes';
    
    /**
    * Account Types
    */
    const ACCOUNT_TYPE_GOOGLE_ANALYTICS = 0;

    const ACCOUNT_TYPE_UNIVERSAL_ANALYTICS = 1;

    /**
     * Anonymize IP Default Yes
     */
    const DEFAULT_YES = 0;

    /**
     * Whether GA is ready to use
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function isGoogleAnalyticsAvailable($store = null)
    {
        $accountId = $this->getAccountId();
        return $accountId && $this->scopeConfig->isSetFlag(self::XML_PATH_ACTIVE, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Whether anonymized IPs are active
     * Google Analytics Accounts(GA4) is always true
     * @param null|string|bool|int|Store $store
     * @return bool
     * @since 100.2.0
     */
    public function isAnonymizedIpActive($store = null)
    {
        if ($this->isGoogleAnalyticsAccount()) {
            return true;
        } else {
            return (bool)$this->scopeConfig->getValue(
                self::XML_PATH_ANONYMIZE, ScopeInterface::SCOPE_STORE, $store);
        }

    }

    /**
    * Get Account Type
    *
    * @return int
    */
    public function getAccountType()
    {
 	    return $this->scopeConfig->getValue(
 	        self::XML_PATH_ACCOUNT_TYPE,\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * Checks if Account Type is Google Analytics Account
     *
     * @return bool
     */
    public function isGoogleAnalyticsAccount()
    {
        return $this->getAccountType() == self::ACCOUNT_TYPE_GOOGLE_ANALYTICS;
    }

    /**
     * Checks if Account Type is Universal Analytics Account
     *
     * @return bool
     */
    public function isUniversalAnalyticsAccount()
    {
        return $this->getAccountType() == self::ACCOUNT_TYPE_UNIVERSAL_ANALYTICS;
    }

    /**
     * Get Account Id, depending on property type Tracking Id (UA) or Measurement Id (GA4)
     *
     * @return string
     */
    public function getAccountId()
    {
        if ($this->isGoogleAnalyticsAccount()) {
            return (string)$this->scopeConfig->getValue(
                self::XML_PATH_MEASUREMENT_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        } else {
            return (string)$this->scopeConfig->getValue(
                self::XML_PATH_TRACKING_ID,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );

        }

    }

    /**
     * Format Data
     *
     * @return Float
     */
    public function formatToDec($numberString)
    {
        return number_format($numberString, 2);
    }
}
