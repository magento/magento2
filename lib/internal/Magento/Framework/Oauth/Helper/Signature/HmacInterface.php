<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Oauth\Helper\Signature;

interface HmacInterface
{
    /**
     * Mapping of signature methods to hash algorithms
     */
    public const HASH_ALGORITHM_MAP = [
        'HMAC-SHA1' => 'sha1',
        'HMAC-SHA256' => 'sha256'
    ];

    /**
     * Default hash algorithm to use if method not found in map
     */
    public const DEFAULT_HASH_ALGORITHM = 'sha256';

    /**
     * Generate signature
     *
     * @param string $url
     * @param array $parameters
     * @param string $consumerSecret
     * @param string $tokenSecret
     * @param string $signatureMethod
     * @param string $method
     * @return string
     */
    public function sign(
        string $url,
        array $parameters,
        string $consumerSecret,
        string $tokenSecret,
        string $signatureMethod,
        string $method = 'POST'
    ): string;
}
