<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Integration\Api\Data;

/**
 * Information attached to a user's token.
 */
interface UserTokenDataInterface
{
    public function getIssued(): \DateTimeImmutable;

    public function getExpires(): \DateTimeImmutable;
}
