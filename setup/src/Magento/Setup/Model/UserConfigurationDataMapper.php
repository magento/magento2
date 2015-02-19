<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Backend\Model\Url;
use Magento\Directory\Helper\Data;
use Magento\Directory\Model\Currency;
use Magento\Setup\Module\Setup;
use Magento\Store\Model\Store;

/**
 * Model Class to Install User Configuration Data
 *
 * @package Magento\Setup\Model
 */
class UserConfigurationDataMapper
{
    /**#@+
     * Model data keys
     */
    const KEY_USE_SEF_URL = 'use_rewrites';
    const KEY_BASE_URL = 'base_url';
    const KEY_BASE_URL_SECURE = 'base_url_secure';
    const KEY_IS_SECURE = 'use_secure';
    const KEY_IS_SECURE_ADMIN = 'use_secure_admin';
    const KEY_LANGUAGE = 'language';
    const KEY_TIMEZONE = 'timezone';
    const KEY_CURRENCY = 'currency';
    const KEY_ADMIN_USE_SECURITY_KEY = 'admin_use_security_key';
    /**#@- */

    /**
     * Map of configuration paths to data keys
     *
     * @var array
     */
    private $pathDataMap = [
        Store::XML_PATH_USE_REWRITES => self::KEY_USE_SEF_URL,
        Store::XML_PATH_UNSECURE_BASE_URL => self::KEY_BASE_URL,
        Store::XML_PATH_SECURE_BASE_URL => self::KEY_BASE_URL_SECURE,
        Data::XML_PATH_DEFAULT_LOCALE => self::KEY_LANGUAGE,
        Data::XML_PATH_DEFAULT_TIMEZONE => self::KEY_TIMEZONE,
        Currency::XML_PATH_CURRENCY_BASE => self::KEY_CURRENCY,
        Currency::XML_PATH_CURRENCY_DEFAULT => self::KEY_CURRENCY,
        Currency::XML_PATH_CURRENCY_ALLOW => self::KEY_CURRENCY,
        Url::XML_PATH_USE_SECURE_KEY => self::KEY_ADMIN_USE_SECURITY_KEY,
    ];

    /**
     * Gets All Configuration Data
     *
     * @param array $installParamData
     * @return array
     */
    public function getConfigData($installParamData)
    {
        $configData = [];
        if (!$this->isSecureUrlNeeded($installParamData) && isset($installParamData[self::KEY_BASE_URL_SECURE])) {
            unset($installParamData[self::KEY_BASE_URL_SECURE]);
        }

        // Base URL is secure, add secure entries
        if (isset($installParamData[self::KEY_BASE_URL_SECURE])) {
            $this->pathDataMap = array_merge(
                $this->pathDataMap,
                [
                    Store::XML_PATH_SECURE_IN_FRONTEND  => self::KEY_IS_SECURE,
                    Store::XML_PATH_SECURE_IN_ADMINHTML => self::KEY_IS_SECURE_ADMIN
                ]
            );
        }

        foreach ($this->pathDataMap as $path => $key) {
            $configData = $this->addParamToConfigData($configData, $installParamData, $key, $path);
        }
        return $configData;
    }

    /**
     * Determine if secure URL is needed (use_secure or use_secure_admin flag is set.)
     *
     * @param array $installParamData
     * @return bool
     */
    private function isSecureUrlNeeded($installParamData)
    {
        return ((isset($installParamData[self::KEY_IS_SECURE]) && $installParamData[self::KEY_IS_SECURE])
            || (isset($installParamData[self::KEY_IS_SECURE_ADMIN]) && $installParamData[self::KEY_IS_SECURE_ADMIN]));
    }

    /**
     * Adds an install parameter value to the configData structure
     *
     * @param array $configData
     * @param array $installParamData
     * @param string $key
     * @param string $path
     * @return array
     */
    private function addParamToConfigData($configData, $installParamData, $key, $path)
    {
        if (isset($installParamData[$key])) {
            if (($key === self::KEY_BASE_URL) || ($key === self::KEY_BASE_URL_SECURE)) {
                $installParamData[$key] = rtrim($installParamData[$key], '/') . '/';
            }
            $configData[$path] = $installParamData[$key];
        }
        return $configData;
    }
}
