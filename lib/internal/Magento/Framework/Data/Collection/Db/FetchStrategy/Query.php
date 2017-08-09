<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Retrieving collection data by querying a database
 */
namespace Magento\Framework\Data\Collection\Db\FetchStrategy;

use Magento\Framework\DB\Select;

/**
 * Class \Magento\Framework\Data\Collection\Db\FetchStrategy\Query
 *
 */
class Query implements \Magento\Framework\Data\Collection\Db\FetchStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function fetchAll(Select $select, array $bindParams = [])
    {
        return $select->getConnection()->fetchAll($select, $bindParams);
    }
}
