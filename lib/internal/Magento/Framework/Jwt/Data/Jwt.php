<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Jwt\Data;

use Jose\Component\Core\JWT as CoreJwt;

/**
 * Proxy DTO to wrap JWT library.
 */
class Jwt
{
    /**
     * @var CoreJwt
     */
    private $jwt;

    /**
     * @param CoreJwt $jwt
     */
    public function __construct(CoreJwt $jwt)
    {
        $this->jwt = $jwt;
    }

    /**
     * Gets JWT.
     *
     * @return CoreJwt|null
     */
    public function getToken(): ?CoreJwt
    {
        return $this->jwt;
    }
}
