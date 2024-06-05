<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Oauth\Helper;

use Laminas\Crypt\Hmac as HMACEncryption;

class Utility
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
        array  $params,
        string $signatureMethod,
        string $consumerSecret,
        ?string $tokenSecret = null,
        ?string $method = null,
        ?string $url = null
    ): string {
        unset($params['oauth_signature']);

        $parts     = explode('-', $signatureMethod);
        $binaryHash = HMACEncryption::compute(
            $this->assembleKey($consumerSecret, $tokenSecret),
            count($parts) > 1 ? $parts[1] : $signatureMethod,
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
            $parts[$key] = $this->urlEncode($secret);
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
            $encodedParams[$this->urlEncode($key)] =
                $this->urlEncode($value);
        }
        $baseStrings = [];
        if (isset($method)) {
            $baseStrings[] = strtoupper($method);
        }
        if (isset($url)) {
            $baseStrings[] = $this->urlEncode($url);
        }
        if (isset($encodedParams['oauth_signature'])) {
            unset($encodedParams['oauth_signature']);
        }
        $baseStrings[] = $this->urlEncode(
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

    /**
     * URL encode a value
     *
     * @param string $value
     * @return string
     */
    private function urlEncode(string $value): string
    {
        $encoded = rawurlencode($value);
        return str_replace('%7E', '~', $encoded);
    }
}
