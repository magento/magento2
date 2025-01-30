<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Oauth\Helper;

use Laminas\OAuth\Http\Utility as LaminasUtility;
use Magento\Framework\Oauth\Helper\Signature\HmacFactory;

class Utility
{
    /**
     * @param LaminasUtility $httpUtility
     * @param HmacFactory $hmacFactory
     */
    public function __construct(private readonly LaminasUtility $httpUtility, private readonly HmacFactory $hmacFactory)
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
            $hmac = $this->hmacFactory->create(
                ['consumerSecret' => $consumerSecret,
                    'tokenSecret' => $tokenSecret,
                    'hashAlgo' => 'sha256'
                ]
            );

            return $hmac->sign($params, $method, $url);
        } else {
            return $this->httpUtility->sign($params, $signatureMethod, $consumerSecret, $tokenSecret, $method, $url);
        }
    }

    /**
     * Check if signature method is HMAC-SHA256
     *
     * @param string $signatureMethod
     * @return bool
     */
    private function isHmac256(string $signatureMethod): bool
    {
        if (strtoupper(preg_replace('/[\W]/', '', $signatureMethod)) === 'HMACSHA256') {
            return true;
        }

        return false;
    }

    /**
     * Cast to authorization header
     *
     * @param array $params
     * @param string|null $realm
     * @param bool $excludeCustomParams
     */
    public function toAuthorizationHeader(array $params, ?string $realm = null, bool $excludeCustomParams = true)
    {
        $authorizationHeader = $this->httpUtility->toAuthorizationHeader($params, $realm, $excludeCustomParams);
        return str_replace('realm="",', '', $authorizationHeader);
    }
}
