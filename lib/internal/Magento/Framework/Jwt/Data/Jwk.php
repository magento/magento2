<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Jwt\Data;

use Jose\Component\Core\JWK as CoreJwk;

/**
 * Proxy DTO to wrap the JWT library.
 */
class Jwk
{
    /**
     * @var CoreJwk
     */
    private $jwk;

    /**
     * @param CoreJwk $jwk
     */
    public function __construct(CoreJwk $jwk)
    {
        $this->jwk = $jwk;
    }

    /**
     * Gets JWK.
     *
     * @return CoreJwk|null
     */
    public function getKey(): ?CoreJwk
    {
        return $this->jwk;
    }
}
