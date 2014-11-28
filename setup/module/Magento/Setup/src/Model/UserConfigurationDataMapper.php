<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Setup\Model;

use Magento\Setup\Module\Setup;
use Magento\Store\Model\Store;
use Magento\Core\Helper\Data;
use Magento\Directory\Model\Currency;
use Magento\Backend\Model\Url;

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
        foreach (self::$pathDataMap as $path => $key) {
            if (isset($data[$key])) {
                $configData[$path] = $data[$key];
            }
        }
        return $configData;
    }
}
