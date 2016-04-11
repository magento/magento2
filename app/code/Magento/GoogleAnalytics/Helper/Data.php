<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
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
}
