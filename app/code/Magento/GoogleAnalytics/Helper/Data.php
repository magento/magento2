<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\GoogleAnalytics\Helper;

use Magento\Store\Model\Store;

/**
 * GoogleAnalytics data helper
 *
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Config paths for using throughout the code
     */
    const XML_PATH_ACTIVE = 'google/analytics/active';

    const XML_PATH_ACCOUNT = 'google/analytics/account';

    const XML_PATH_ANONYMIZE = 'google/analytics/anonymize';

    /**
     * Whether GA is ready to use
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function isGoogleAnalyticsAvailable($store = null)
    {
        $accountId = $this->scopeConfig->getValue(self::XML_PATH_ACCOUNT, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
        return $accountId && $this->scopeConfig->isSetFlag(self::XML_PATH_ACTIVE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Whether anonymized IPs are active
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function isAnonymizedIpActive($store = null) {
        $anonymize = $this->scopeConfig->getValue(self::XML_PATH_ANONYMIZE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
        return $anonymize;
    }

}
