<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Core URL helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Url\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Retrieve current url in base64 encoding
     *
     * @return string
     */
    public function getCurrentBase64Url()
    {
        return $this->urlEncoder->encode($this->_urlBuilder->getCurrentUrl());
    }

    /**
     * @param string $url
     * @return string
     */
    public function getEncodedUrl($url = null)
    {
        if (!$url) {
            $url = $this->_urlBuilder->getCurrentUrl();
        }
        return $this->urlEncoder->encode($url);
    }

    /**
     * Add request parameter into url
     *
     * @param  string $url
     * @param  array $param array( 'key' => value )
     * @return string
     */
    public function addRequestParam($url, $param)
    {
        $startDelimiter = false === strpos($url, '?') ? '?' : '&';

        $arrQueryParams = [];
        foreach ($param as $key => $value) {
            if (is_numeric($key) || is_object($value)) {
                continue;
            }

            if (is_array($value)) {
                // $key[]=$value1&$key[]=$value2 ...
                $arrQueryParams[] = $key . '[]=' . implode('&' . $key . '[]=', $value);
            } elseif ($value === null) {
                $arrQueryParams[] = $key;
            } else {
                $arrQueryParams[] = $key . '=' . $value;
            }
        }
        if (!empty($arrQueryParams)) {
            $url .= $startDelimiter . implode('&', $arrQueryParams);
        }

        return $url;
    }

    /**
     * Remove request parameter from url
     * @param string $url
     * @param string $paramKey
     * @param bool $caseSensitive
     * @return string
     */
    public function removeRequestParam($url, $paramKey, $caseSensitive = false)
    {
        $regExpression = '/\\?[^#]*?(' . preg_quote($paramKey, '/') . '\\=[^#&]*&?)/' . ($caseSensitive ? '' : 'i');
        while (preg_match($regExpression, $url, $matches) !== 0) {
            $paramString = $matches[1];
            // if ampersand is at the end of $paramString
            if (substr($paramString, -1, 1) != '&') {
                $url = preg_replace('/(&|\\?)?' . preg_quote($paramString, '/') . '/', '', $url);
            } else {
                $url = str_replace($paramString, '', $url);
            }
        }
        return $url;
    }
}
