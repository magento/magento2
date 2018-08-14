<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Model\ResourceModel;

/**
 * Integration resource model
 */
class Integration extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('integration', 'integration_id');
    }

    /**
     * Select token for a given customer.
     *
     * @param int $consumerId
     * @return array|boolean - Row data (array) or false if there is no corresponding row
     */
    public function selectActiveIntegrationByConsumerId($consumerId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable())
            ->where('consumer_id = ?', $consumerId)
            ->where('status = ?', \Magento\Integration\Model\Integration::STATUS_ACTIVE);
        return $connection->fetchRow($select);
    }
}
