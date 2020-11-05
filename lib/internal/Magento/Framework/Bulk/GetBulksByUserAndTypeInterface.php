<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Bulk;

/**
 * Interface GetBulksByUserAndTypeInterface
 * @api
 */
interface GetBulksByUserAndTypeInterface
{
    /**
     * Returns all bulks created by user and user type
     *
     * @param int $userId
     * @param int $userTypeId
     * @return BulkSummaryInterface[]
     */
    public function execute(int $userId, int $userTypeId): array;
}
