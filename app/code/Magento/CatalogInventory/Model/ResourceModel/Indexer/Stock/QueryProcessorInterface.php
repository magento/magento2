<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\CatalogInventory\Model\ResourceModel\Indexer\Stock;

use Magento\Framework\DB\Select;

/**
 * @api
 * @since 2.1.0
 */
interface QueryProcessorInterface
{
    /**
     * @param Select $select
     * @param null|array $entityIds
     * @param bool $usePrimaryTable
     * @return Select
     * @since 2.1.0
     */
    public function processQuery(Select $select, $entityIds = null, $usePrimaryTable = false);
}
