<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;

/**
 * Signifyd integration configuration.
 *
 * Class is a proxy service for retrieving configuration settings.
 */
class Config
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        EncryptorInterface $encryptor
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->encryptor = $encryptor;
    }

    /**
     * If this config option set to false no Signifyd integration should be available
     * (only possibility to configure Signifyd setting in admin)
     *
     * @return bool
     */
    public function isEnabled()
    {
        $enabled = $this->scopeConfig->isSetFlag('fraud_protection/signifyd/active');
        return $enabled;
    }

    /**
     * Signifyd API Key used for authentication.
     *
     * @see https://www.signifyd.com/docs/api/#/introduction/authentication
     * @see https://app.signifyd.com/settings
     *
     * @return string
     */
    public function getApiKey()
    {
        $encryptedApiKey = $this->scopeConfig->getValue('fraud_protection/signifyd/api_key');
        $apiKey = $this->encryptor->decrypt($encryptedApiKey);
        return $apiKey;
    }

    /**
     * Base URL to Signifyd REST API.
     * Usually equals to https://api.signifyd.com/v2 and should not be changed
     *
     * @return string
     */
    public function getApiUrl()
    {
        $apiUrl = $this->scopeConfig->getValue('fraud_protection/signifyd/api_url');
        return $apiUrl;
    }

    /**
     * If is "true" extra information about interaction with Signifyd API are written to debug.log file
     *
     * @return bool
     */
    public function isDebugModeEnabled()
    {
        $debugModeEnabled = $this->scopeConfig->isSetFlag('fraud_protection/signifyd/debug');
        return $debugModeEnabled;
    }

}