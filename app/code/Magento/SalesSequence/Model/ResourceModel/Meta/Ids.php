<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesSequence\Model\ResourceModel\Meta;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Ids is used to retrieve metadata ids for sequence
 */
class Ids extends AbstractDb
{
    /**
     * Model initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_sequence_meta', 'meta_id');
    }

    /**
     * Retrieves Metadata Ids by store id
     *
     * @param int $storeId
     * @return int[]
     * @throws LocalizedException
     */
    public function getByStoreId($storeId)
    {
        $connection = $this->getConnection();
        $bind = ['store_id' => $storeId];
        $select = $connection->select()->from(
            $this->getMainTable(),
            [$this->getIdFieldName()]
        )->where(
            'store_id = :store_id'
        );

        return $connection->fetchCol($select, $bind);
    }
}
