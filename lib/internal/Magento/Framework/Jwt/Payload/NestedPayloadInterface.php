<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Payload;

use Magento\Framework\Jwt\PayloadInterface;

/**
 * Payload with nested JWT.
 */
interface NestedPayloadInterface extends PayloadInterface
{
    public const CONTENT_TYPE = 'JWT';
}
