<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Jwt\KeyGenerator;

use Magento\Framework\Jwt\Data\Jwk;
use Magento\Framework\Jwt\KeyGeneratorInterface;

/**
 * Generates JWK from simple string
 */
class StringKey implements KeyGeneratorInterface
{
    /**
     * @var SecretKeyFactory
     */
    private $keyFactory;

    /**
     * @var string
     */
    private $key;

    /**
     * @param SecretKeyFactory $keyFactory
     * @param string $key
     */
    public function __construct(SecretKeyFactory $keyFactory, string $key)
    {
        $this->keyFactory = $keyFactory;
        $this->key = $key;
    }

    /**
     * Creates JWK based on a provided key.
     *
     * @return Jwk
     */
    public function generate(): Jwk
    {
        return $this->keyFactory->create($this->key);
    }
}
