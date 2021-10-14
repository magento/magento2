<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Payload;

use Magento\Framework\Jwt\ClaimInterface;
use Magento\Framework\Jwt\PayloadInterface;

/**
 * Payload with claims.
 */
interface ClaimsPayloadInterface extends PayloadInterface
{
    /**
     * Claims array with claim names as keys.
     *
     * @return ClaimInterface[]
     */
    public function getClaims(): array;
}
