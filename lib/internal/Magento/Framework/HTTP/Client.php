<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
     * @return \Magento\Framework\HTTP\ClientInterface
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
