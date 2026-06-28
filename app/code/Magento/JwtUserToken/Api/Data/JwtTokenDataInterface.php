<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\JwtUserToken\Api\Data;

use Magento\Framework\Jwt\HeaderInterface;
use Magento\Framework\Jwt\Payload\ClaimsPayloadInterface;
use Magento\Integration\Api\Data\UserTokenDataInterface;

/**
 * Adds JWT data retrieved from a token.
 */
interface JwtTokenDataInterface extends UserTokenDataInterface
{
    public function getJwtHeader(): HeaderInterface;

    public function getJwtClaims(): ClaimsPayloadInterface;
}
