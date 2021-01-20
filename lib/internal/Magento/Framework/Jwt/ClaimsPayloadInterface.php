<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt;

/**
 * Payload with claims.
 */
interface ClaimsPayloadInterface extends PayloadInterface
{
    /**
     * Claims.
     *
     * @return ClaimInterface[]
     */
    public function getClaims(): array;
}
