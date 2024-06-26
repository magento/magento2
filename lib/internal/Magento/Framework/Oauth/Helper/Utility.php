<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Oauth\Helper;

use Laminas\OAuth\Http\Utility as LaminasUtility;
use Magento\Framework\Oauth\Helper\Signature\Hmac256;

class Utility
{
    /**
     * @param LaminasUtility $httpUtility
     * @param Hmac256 $hmac256
     */
    public function __construct(private readonly LaminasUtility $httpUtility, private readonly Hmac256 $hmac256)
    {
    }

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
        if ($this->isHmac256($signatureMethod)) {
            return $this->hmac256->sign($params, 'sha256', $consumerSecret, $tokenSecret, $method, $url);
        } else {
            return $this->httpUtility->sign($params, $signatureMethod, $consumerSecret, $tokenSecret, $method, $url);
        }
    }

    /**
     * Check if signature method is HMAC256
     *
     * @param string $signatureMethod
     * @return bool
     */
    private function isHmac256(string $signatureMethod): bool
    {
        if (strtoupper(preg_replace( '/[\W]/', '', $signatureMethod)) === 'HMAC256') {
            return true;
        }

        return false;
    }

    /**
     * Cast to authorization header
     *
     * @param array $params
     * @param bool $excludeCustomParams
     * @return string
     */
    public function toAuthorizationHeader(array $params, bool $excludeCustomParams = true): string
    {
        $headerValue = [];
        foreach ($params as $key => $value) {
            if ($excludeCustomParams) {
                if (! preg_match("/^oauth_/", $key)) {
                    continue;
                }
            }
            $headerValue[] = $this->httpUtility::urlEncode((string)$key)
                . '="'
                . $this->httpUtility::urlEncode((string)$value) . '"';
        }
        return 'OAuth ' . implode(",", $headerValue);
    }
}
