<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model;

use Magento\Backend\Model\Url;
use Magento\Core\Helper\Data;
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
    private static $pathDataMap = [
        Store::XML_PATH_USE_REWRITES => self::KEY_USE_SEF_URL,
        Store::XML_PATH_UNSECURE_BASE_URL => self::KEY_BASE_URL,
        Store::XML_PATH_SECURE_IN_FRONTEND => self::KEY_IS_SECURE,
        Store::XML_PATH_SECURE_BASE_URL => self::KEY_BASE_URL_SECURE,
        Store::XML_PATH_SECURE_IN_ADMINHTML => self::KEY_IS_SECURE_ADMIN,
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
     * @param array $data
     * @return array
     */
    public function getConfigData($data)
    {
        $configData = [];
        if (!((isset($data[self::KEY_IS_SECURE]) && $data[self::KEY_IS_SECURE])
            || (isset($data[self::KEY_IS_SECURE_ADMIN]) && $data[self::KEY_IS_SECURE_ADMIN]))
            && isset($data[self::KEY_BASE_URL_SECURE])) {
            unset($data[self::KEY_BASE_URL_SECURE]);
        }
        foreach (self::$pathDataMap as $path => $key) {
            if (isset($data[$key])) {
                if ((($key === self::KEY_IS_SECURE) || ($key === self::KEY_IS_SECURE_ADMIN))
                    && (!isset($data[self::KEY_BASE_URL_SECURE]))) {
                    continue;
                }
                if (($key === self::KEY_BASE_URL) || ($key === self::KEY_BASE_URL_SECURE)) {
                    $data[$key] = rtrim($data[$key], '/') . '/';
                }
                $configData[$path] = $data[$key];
            }
        }
        return $configData;
    }
}
