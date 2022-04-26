<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleGtag\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

/**
 * GoogleAnalytics data helper
 *
 * @api
 */
class Data extends AbstractHelper
{
    /**
     * Config paths for using throughout the code
     */
    public const XML_PATH_ACTIVE = 'google/gtag/analytics4/active';

    public const XML_PATH_MEASUREMENT_ID = 'google/gtag/analytics4/measurement_id';

    /**
     * Anonymize IP Default Yes
     */
    public const DEFAULT_YES = 1;

    /**#@-*/

    /**#@+
     * Google AdWords conversion src
     */
    public const GTAG_GLOBAL_SITE_TAG_SRC = 'https://www.googletagmanager.com/gtag/js?id=';

    /**#@+
     * Google AdWords config data
     */
    public const XML_PATH_ADWORD_ACTIVE = 'google/gtag/adwords/active';

    public const XML_PATH_CONVERSION_ID = 'google/gtag/adwords/conversion_id';

    public const XML_PATH_CONVERSION_LABEL = 'google/gtag/adwords/conversion_label';

    /**
     * Whether GA is ready to use
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function isGoogleAnalyticsAvailable($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ACTIVE,
            ScopeInterface::SCOPE_STORE,
            $store
        ) && $this->getAccountId();
    }

    /**
     * Get Account Id, depending on property type Tracking Id (UA) or Measurement Id (GA4)
     *
     * @return string
     */
    public function getAccountId()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_MEASUREMENT_ID,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is Google AdWords active
     *
     * @return bool
     */
    public function isGoogleAdwordsActive()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ADWORD_ACTIVE,
            ScopeInterface::SCOPE_STORE
        ) &&
            $this->getConversionId() &&
            $this->getConversionLabel();
    }

    /**
     * Is Google AdWords congifurable
     *
     * @return bool
     */
    public function isGoogleAdwordsConfigurable()
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ADWORD_ACTIVE,
            ScopeInterface::SCOPE_STORE
        ) && $this->getConversionId();
    }

    /**
     * Get conversion js src
     *
     * @return string
     */
    public function getConversionGtagGlobalSiteTagSrc()
    {
        $siteSrc = self::GTAG_GLOBAL_SITE_TAG_SRC;
        $cId = $this->getConversionId();
        return $siteSrc . $cId;
    }

    /**
     * Get Google AdWords conversion id
     *
     * @return string
     */
    public function getConversionId()
    {
        return (string)$this->scopeConfig->getValue(
            self::XML_PATH_CONVERSION_ID,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Google AdWords conversion label
     *
     * @return string
     */
    public function getConversionLabel()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONVERSION_LABEL,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Format Data
     *
     * @param float $numberString
     * @return Float
     */
    public function formatToDec($numberString)
    {
        return number_format($numberString, 2);
    }
}
