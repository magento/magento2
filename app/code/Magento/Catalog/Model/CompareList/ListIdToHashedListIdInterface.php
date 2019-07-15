<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\CompareList;

/**
 * Converts list id to the list hashed id.
 * @api
 */
interface ListIdToHashedListIdInterface
{
    /**
     * @param int $listId
     *
     * @return string
     */
    public function execute(int $listId): string;
}
