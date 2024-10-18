<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleGtag\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;

class GtagConfig
{
    /**
     * Config paths for using throughout the code
     */
    private const XML_PATH_ACTIVE = 'google/gtag/analytics4/active';

    private const XML_PATH_MEASUREMENT_ID = 'google/gtag/analytics4/measurement_id';

    /**
     * Google AdWords conversion src
     */
    private const GTAG_GLOBAL_SITE_TAG_SRC = 'https://www.googletagmanager.com/gtag/js?id=';

    /**#@+
     * Google AdWords config data
     */
    private const XML_PATH_ADWORD_ACTIVE = 'google/gtag/adwords/active';

    private const XML_PATH_CONVERSION_ID = 'google/gtag/adwords/conversion_id';

    private const XML_PATH_CONVERSION_LABEL = 'google/gtag/adwords/conversion_label';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Whether GA is ready to use
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function isGoogleAnalyticsAvailable($store = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ACTIVE,
            ScopeInterface::SCOPE_STORE,
            $store
        ) && $this->getMeasurementId();
    }

    /**
     * Get Measurement Id (GA4)
     *
     * @return string
     */
    public function getMeasurementId(): string
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
    public function isGoogleAdwordsActive(): bool
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
    public function isGoogleAdwordsConfigurable(): bool
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
    public function getConversionGtagGlobalSiteTagSrc(): string
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
    public function getConversionId(): string
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
    public function getConversionLabel(): string
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CONVERSION_LABEL,
            ScopeInterface::SCOPE_STORE
        );
    }
}
