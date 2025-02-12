<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtUserToken\Api;

use Magento\JwtUserToken\Api\Data\Revoked;

/**
 * Repository for revoked tokens data.
 */
interface RevokedRepositoryInterface
{
    /**
     * Store revoked tokens data.
     *
     * @param Revoked $revoked
     * @return void
     */
    public function saveRevoked(Revoked $revoked): void;

    /**
     * Find user's record.
     *
     * @param int $userTypeId
     * @param int $userId
     * @return Revoked|null
     */
    public function findRevoked(int $userTypeId, int $userId): ?Revoked;
}
