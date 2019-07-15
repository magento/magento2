<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\CompareList;

/**
 * Converts hashed list id to the list id.
 * @api
 */
interface HashedListIdToListIdInterface
{
    /**
     * @param string $hashedListId
     *
     * @return int
     */
    public function execute(string $hashedListId): int;
}
