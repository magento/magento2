<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Oauth\Helper;

use Magento\Framework\Oauth\Helper\Signature\HmacInterface;

class Utility
{
    /**
     * @var HmacInterface
     */
    private HmacInterface $hmac;

    /**
     * @param HmacInterface $hmac
     */
    public function __construct(
        HmacInterface $hmac
    ) {
        $this->hmac = $hmac;
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
        array $params,
        string $signatureMethod,
        string $consumerSecret,
        ?string $tokenSecret = null,
        ?string $method = null,
        ?string $url = null
    ): string {
        return $this->hmac->sign(
            $url ?? '',
            $params,
            $consumerSecret,
            $tokenSecret ?? '',
            $signatureMethod,
            $method ?? 'POST'
        );
    }

    /**
     * Cast to authorization header
     *
     * @param array $params
     * @param string|null $realm
     * @param bool $excludeCustomParams
     * @return string
     */
    public function toAuthorizationHeader(array $params, ?string $realm = null, bool $excludeCustomParams = true)
    {
        $header = 'OAuth ';
        if ($realm) {
            $header .= 'realm="' . rawurlencode($realm) . '", ';
        }
        $values = [];
        foreach ($params as $key => $value) {
            if ($excludeCustomParams && substr($key, 0, 6) !== 'oauth_') {
                continue;
            }
            $values[] = rawurlencode($key) . '="' . rawurlencode($value) . '"';
        }
        $header .= implode(', ', $values);
        return $header;
    }
}
