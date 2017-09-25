<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Model;

/**
 * Class OrderIncrementIdChecker
 * Check if order increment ID is already used.
 */
class OrderIncrementIdChecker
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order
     */
    private $resourceModel;

    /**
     * OrderIncrementIdChecker constructor.
     * @param ResourceModel\Order $resourceModel
     */
    public function __construct(ResourceModel\Order $resourceModel)
    {
        $this->resourceModel = $resourceModel;
    }

    /**
     * Check if order increment ID is already used.
     * Method can be used to avoid collisions of order IDs.
     *
     * @param int $orderIncrementId
     * @return bool
     */
    public function isIncrementIdUsed($orderIncrementId)
    {
        /** @var  \Magento\Framework\DB\Adapter\AdapterInterface $adapter */
        $adapter = $this->resourceModel->getConnection();
        $bind = [':increment_id' => $orderIncrementId];
        /** @var \Magento\Framework\DB\Select $select */
        $select = $adapter->select();
        $select->from($this->resourceModel->getMainTable(), 'entity_id')->where('increment_id = :increment_id');
        $entity_id = $adapter->fetchOne($select, $bind);
        if ($entity_id > 0) {
            return true;
        }

        return false;
    }
}
