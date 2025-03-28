<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Oauth\Helper\Signature;

class Hmac implements HmacInterface
{
    /**
     * @inheritDoc
     */
    public function sign(
        string $url,
        array $parameters,
        string $consumerSecret,
        string $tokenSecret,
        string $signatureMethod,
        string $method = 'POST'
    ): string {
        $hashAlgorithm = $this->getHashAlgorithm($signatureMethod);
        $baseString = $this->createBaseString($url, $parameters, $method);
        $signingKey = $this->getSigningKey($consumerSecret, $tokenSecret);
        return base64_encode($this->hash($baseString, $signingKey, $hashAlgorithm));
    }

    /**
     * Get hash algorithm from signature method
     *
     * @param string $signatureMethod
     * @return string
     */
    private function getHashAlgorithm(string $signatureMethod): string
    {
        return self::HASH_ALGORITHM_MAP[$signatureMethod] ?? self::DEFAULT_HASH_ALGORITHM;
    }

    /**
     * Generate signature base string
     *
     * @param string $url
     * @param array $parameters
     * @param string $method
     * @return string
     */
    private function createBaseString(string $url, array $parameters, string $method): string
    {
        $requestUrl = $this->buildRequestUrl($url);
        $requestParams = $this->buildRequestParams($url, $parameters);
        $normalizedParams = $this->normalizeParameters($requestParams);

        return sprintf(
            '%s&%s&%s',
            strtoupper($method),
            rawurlencode($requestUrl),
            rawurlencode(implode('&', $normalizedParams))
        );
    }

    /**
     * Create signing key
     *
     * @param string $consumerSecret
     * @param string $tokenSecret
     * @return string
     */
    private function getSigningKey(string $consumerSecret, string $tokenSecret): string
    {
        return sprintf(
            '%s&%s',
            rawurlencode($consumerSecret),
            rawurlencode($tokenSecret)
        );
    }

    /**
     * Hash the signature
     *
     * @param string $string
     * @param string $key
     * @param string $algorithm
     * @return string
     */
    private function hash(string $string, string $key, string $algorithm): string
    {
        return hash_hmac($algorithm, $string, $key, true);
    }

    /**
     * Build request URL from URL parts
     *
     * @param string $url
     * @return string
     */
    private function buildRequestUrl(string $url): string
    {
        $urlParts = parse_url($url);
        $requestUrl = $urlParts['scheme'] . '://' . $urlParts['host'];
        if (isset($urlParts['port'])) {
            $requestUrl .= ':' . $urlParts['port'];
        }
        return $requestUrl . ($urlParts['path'] ?? '');
    }

    /**
     * Build request parameters including URL query parameters
     *
     * @param string $url
     * @param array $parameters
     * @return array
     */
    private function buildRequestParams(string $url, array $parameters): array
    {
        $urlParts = parse_url($url);
        $requestParams = $parameters;

        if (isset($urlParts['query'])) {
            parse_str($urlParts['query'], $urlQueryParams);
            $requestParams = array_merge($requestParams, $urlQueryParams);
        }

        return $this->removeNestedArrays($requestParams);
    }

    /**
     * Remove nested arrays from parameters
     *
     * @param array $params
     * @return array
     */
    private function removeNestedArrays(array $params): array
    {
        foreach (array_keys($params) as $key) {
            if (str_contains($key, '[') && str_contains($key, ']')) {
                $baseKey = substr($key, 0, strpos($key, '['));
                if (isset($params[$baseKey]) && is_array($params[$baseKey])) {
                    unset($params[$baseKey]);
                }
            }
        }
        return $params;
    }

    /**
     * Normalize parameters for signature
     *
     * @param array $params
     * @return array
     */
    private function normalizeParameters(array $params): array
    {
        ksort($params);
        $normalizedParams = [];

        foreach ($params as $paramKey => $paramValue) {
            if ($paramKey === 'oauth_signature') {
                continue;
            }

            if ($this->isValidValue($paramKey, $paramValue)) {
                $normalizedParams[] = $this->formatParameter($paramKey, $paramValue);
            }
        }

        return $normalizedParams;
    }

    /**
     * Check if parameter value is valid for normalization
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    private function isValidValue(string $key, mixed $value): bool
    {
        if (is_scalar($value) || is_null($value)) {
            return true;
        }

        return is_array($value) && str_contains($key, '[');
    }

    /**
     * Format parameter for signature
     *
     * @param string $key
     * @param mixed $value
     * @return string
     */
    private function formatParameter(string $key, mixed $value): string
    {
        return rawurlencode($key) . '=' . rawurlencode((string)$value);
    }
}
