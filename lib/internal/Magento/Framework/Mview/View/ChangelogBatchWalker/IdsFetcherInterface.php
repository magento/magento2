<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Mview\View\ChangelogBatchWalker;

use Magento\Framework\DB\Select;

/**
 * Interface \Magento\Framework\Mview\View\ChangelogBatchWalker\IdsFetcherInterface
 *
 */
interface IdsFetcherInterface
{
    /**
     * Fetch ids of changed entities
     *
     * @param \Magento\Framework\DB\Select $select
     * @return array
     */
    public function fetch(Select $select): array;
}
