<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Jwt\KeyGenerator;

use Jose\Component\KeyManagement\JWKFactory;
use Magento\Framework\Jwt\Data\Jwk;

/**
 * Creates a JW key based on secret string key.
 */
class SecretKeyFactory
{
    /**
     * Creates JWK from secret string key.
     *
     * @param string $key
     * @return Jwk
     */
    public function create(string $key): Jwk
    {
        $jwk = JWKFactory::createFromSecret(
            $key,
            ['use' => 'sig']
        );

        return new Jwk($jwk);
    }
}
