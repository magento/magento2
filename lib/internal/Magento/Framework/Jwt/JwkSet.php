<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt;

/**
 * Set of JWKs.
 */
class JwkSet
{
    /**
     * @var Jwk[]
     */
    private $keys;

    /**
     * @param Jwk[] $keys
     */
    public function __construct(array $keys)
    {
        $this->keys = $keys;
    }

    /**
     * @return Jwk[]
     */
    public function getKeys(): array
    {
        return $this->keys;
    }
}
