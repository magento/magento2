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

/**
 * Factory for HTTP client classes
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\HTTP;

class Client
{
    /**
     * Disallow to instantiate - pvt constructor
     */
    private function __construct()
    {
    }

    /**
     * Factory for HTTP client
     * @param string/false $frontend  'curl'/'socket' or false for auto-detect
     * @return \Magento\Framework\HTTP\IClient
     */
    public static function getInstance($frontend = false)
    {
        if (false === $frontend) {
            $frontend = self::detectFrontend();
        }
        if (false === $frontend) {
            throw new \Exception("Cannot find frontend automatically, set it manually");
        }

        $class = __CLASS__ . "_" . str_replace(' ', '/', ucwords(str_replace('_', ' ', $frontend)));
        $obj = new $class();
        return $obj;
    }

    /**
     * Detects frontend type.
     * Priority is given to CURL
     * 
     * @return string/bool
     */
    protected static function detectFrontend()
    {
        if (function_exists("curl_init")) {
            return "curl";
        }
        if (function_exists("fsockopen")) {
            return "socket";
        }
        return false;
    }
}
