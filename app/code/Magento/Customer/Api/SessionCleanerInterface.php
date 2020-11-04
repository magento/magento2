<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Api;

/**
 * Interface for cleaning customer session data.
 */
interface SessionCleanerInterface
{
    /**
     * Destroy all active customer sessions related to given customer except current session.
     *
     * @param int $customerId
     * @return void
     */
    public function clearFor(int $customerId): void;
}
