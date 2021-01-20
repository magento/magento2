<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt;

/**
 * Payload with nested JWT.
 */
interface NestedPayloadInterface extends PayloadInterface
{
    /**
     * JWT Content.
     *
     * @return JwtInterface
     */
    public function getJwt(): JwtInterface;
}
