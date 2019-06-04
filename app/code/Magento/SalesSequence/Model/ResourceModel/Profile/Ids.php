<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesSequence\Model\ResourceModel\Profile;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Ids is used to retrieve profile ids for sequence profile
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
        $this->_init('sales_sequence_profile', 'profile_id');
    }

    /**
     * Get profile ids by metadata ids
     *
     * @param int[] $metadataIds
     * @return int[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getByMetadataIds(array $metadataIds)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable(), ['profile_id'])
            ->where('meta_id IN (?)', $metadataIds);

        return $connection->fetchCol($select);
    }
}
