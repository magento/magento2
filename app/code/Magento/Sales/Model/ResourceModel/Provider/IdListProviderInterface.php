<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\ResourceModel\Provider;

/**
 * Interface provides entities id list that should be updated in grid
 */
interface IdListProviderInterface
{
    /**
     * Returns id list of entities for adding or updating in grid.
     *
     * @param string $mainTableName source table name
     * @param string $gridTableName grid table name
     * @return array
     */
    public function get($mainTableName, $gridTableName);
}
