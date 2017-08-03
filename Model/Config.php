<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Signifyd integration configuration.
 *
 * Class is a proxy service for retrieving configuration settings.
 * @since 2.2.0
 */
class Config
{
    /**
     * @var ScopeConfigInterface
     * @since 2.2.0
     */
    private $scopeConfig;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @since 2.2.0
     */
    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * If this config option set to false no Signifyd integration should be available
     * (only possibility to configure Signifyd setting in admin)
     *
     * @return bool
     * @since 2.2.0
     */
    public function isActive()
    {
        $enabled = $this->scopeConfig->isSetFlag(
            'fraud_protection/signifyd/active',
            ScopeInterface::SCOPE_STORE
        );
        return $enabled;
    }

    /**
     * Signifyd API Key used for authentication.
     *
     * @see https://www.signifyd.com/docs/api/#/introduction/authentication
     * @see https://app.signifyd.com/settings
     *
     * @return string
     * @since 2.2.0
     */
    public function getApiKey()
    {
        $apiKey = $this->scopeConfig->getValue(
            'fraud_protection/signifyd/api_key',
            ScopeInterface::SCOPE_STORE
        );
        return $apiKey;
    }

    /**
     * Base URL to Signifyd REST API.
     * Usually equals to https://api.signifyd.com/v2 and should not be changed
     *
     * @return string
     * @since 2.2.0
     */
    public function getApiUrl()
    {
        $apiUrl = $this->scopeConfig->getValue(
            'fraud_protection/signifyd/api_url',
            ScopeInterface::SCOPE_STORE
        );
        return $apiUrl;
    }

    /**
     * If is "true" extra information about interaction with Signifyd API are written to debug.log file
     *
     * @return bool
     * @since 2.2.0
     */
    public function isDebugModeEnabled()
    {
        $debugModeEnabled = $this->scopeConfig->isSetFlag(
            'fraud_protection/signifyd/debug',
            ScopeInterface::SCOPE_STORE
        );
        return $debugModeEnabled;
    }
}
