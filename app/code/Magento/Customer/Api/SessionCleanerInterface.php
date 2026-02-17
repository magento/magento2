<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Api;

/**
 * Interface for cleaning customer session data.
 *
 * @api
 */
interface SessionCleanerInterface
{
    /**
     * Destroy all active customer sessions related to given customer id, including current session.
     *
     * @param int $customerId
     * @return void
     */
    public function clearFor(int $customerId): void;
}
