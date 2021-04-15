<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Collection\Db;

use Magento\Framework\DB\Select;

/**
 * Interface \Magento\Framework\Data\Collection\Db\FetchStrategyInterface
 *
 * @api
 */
interface FetchStrategyInterface
{
    /**
     * Retrieve all records
     *
     * @param Select $select
     * @param array $bindParams
     * @return array
     */
    public function fetchAll(Select $select, array $bindParams = []);
}
