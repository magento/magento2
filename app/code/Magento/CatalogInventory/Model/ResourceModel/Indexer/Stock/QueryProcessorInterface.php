<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock;

use Magento\Framework\DB\Select;

interface QueryProcessorInterface
{
    /**
     * @param Select $select
     * @param null|array $entityIds
     * @param bool $usePrimaryTable
     * @return Select
     */
    public function processQuery(Select $select, $entityIds = null, $usePrimaryTable = false);
}
