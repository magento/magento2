<?php
/************************************************************************
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\Framework\Oauth\Helper;

use Laminas\Crypt\Hmac as HMACEncryption;
use Laminas\OAuth\Http\Utility as HTTPUtility;

class Utility extends HTTPUtility
{
    /**
     * Generate signature string
     *
     * @param array $params
     * @param string $signatureMethod
     * @param string $consumerSecret
     * @param string|null $tokenSecret
     * @param string|null $method
     * @param string|null $url
     * @return string
     */
    public function sign(
        array $params,
        $signatureMethod,
        $consumerSecret,
        $tokenSecret = null,
        $method = null,
        $url = null
    ): string {
        unset($params['oauth_signature']);

        $binaryHash = HMACEncryption::compute(
            $this->assembleKey($consumerSecret, $tokenSecret),
            $signatureMethod,
            $this->getBaseSignatureString($params, $method, $url),
            HMACEncryption::OUTPUT_BINARY
        );

        return base64_encode($binaryHash);
    }

    /**
     * Assemble key from consumer and token secrets
     *
     * @param string $consumerSecret
     * @param string|null $tokenSecret
     * @return string
     */
    private function assembleKey(string $consumerSecret, ?string $tokenSecret): string
    {
        $parts = [$consumerSecret];
        if ($tokenSecret !== null) {
            $parts[] = $tokenSecret;
        }
        foreach ($parts as $key => $secret) {
            $parts[$key] = self::urlEncode($secret);
        }

        return implode('&', $parts);
    }

    /**
     * Get base signature string
     *
     * @param  array $params
     * @param  null|string $method
     * @param  null|string $url
     * @return string
     */
    private function getBaseSignatureString(array $params, $method = null, $url = null): string
    {
        $encodedParams = [];
        foreach ($params as $key => $value) {
            $encodedParams[self::urlEncode($key)] =
                self::urlEncode($value);
        }
        $baseStrings = [];
        if (isset($method)) {
            $baseStrings[] = strtoupper($method);
        }
        if (isset($url)) {
            $baseStrings[] = self::urlEncode($url);
        }
        if (isset($encodedParams['oauth_signature'])) {
            unset($encodedParams['oauth_signature']);
        }
        $baseStrings[] = self::urlEncode(
            $this->toByteValueOrderedQueryString($encodedParams)
        );

        return implode('&', $baseStrings);
    }

    /**
     * Transform an array to a byte value ordered query string
     *
     * @param  array $params
     * @return string
     */
    private function toByteValueOrderedQueryString(array $params): string
    {
        $return = [];
        uksort($params, 'strnatcmp');
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                natsort($value);
                foreach ($value as $keyduplicate) {
                    $return[] = $key . '=' . $keyduplicate;
                }
            } else {
                $return[] = $key . '=' . $value;
            }
        }
        return implode('&', $return);
    }
}
