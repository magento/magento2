<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Integration\Model\ResourceModel;

/**
 * Integration resource model
 * @since 2.0.0
 */
class Integration extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     * @since 2.0.0
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
     * @since 2.0.0
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
