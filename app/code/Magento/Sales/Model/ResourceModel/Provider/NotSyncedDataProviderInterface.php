<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Provider;

/**
 * Interface provides entities id list that should be updated in grid
 * @since 2.2.0
 */
interface NotSyncedDataProviderInterface
{
    /**
     * Returns id list of entities for adding or updating in grid.
     *
     * @param string $mainTableName source table name
     * @param string $gridTableName grid table name
     * @return array
     * @since 2.2.0
     */
    public function getIds($mainTableName, $gridTableName);
}
