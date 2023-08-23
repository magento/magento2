<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
