<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Model\ResourceModel;

use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Inventory\Setup\InstallSchema;

class SourceCarrierLink extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(InstallSchema::TABLE_NAME_SOURCE_CARRIER_LINK, 'source_carrier_link_id');
    }

    /**
     * Delete all source carrier links by sourceId.
     *
     * @param SourceInterface $sourceId
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function deleteBySource(SourceInterface $source)
    {
        $connection = $this->getConnection();

        $connection->delete(
            $connection->getTableName(InstallSchema::TABLE_NAME_SOURCE_CARRIER_LINK),
            $connection->quoteInto('source_id = ?', $source->getId())
        );

        return $this;
    }
}
