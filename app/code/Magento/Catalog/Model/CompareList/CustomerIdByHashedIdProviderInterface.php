<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\CompareList;

/**
 * Get customer id by hashed id.
 * @api
 */
interface CustomerIdByHashedIdProviderInterface
{
    /**
     * @param string $hashedListId
     *
     * @return int
     */
    public function get(string $hashedListId): int;
}
