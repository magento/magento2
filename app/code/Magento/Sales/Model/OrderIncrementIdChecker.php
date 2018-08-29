<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model;

/**
 * This class uses for checking if reserved order id was already used for some order.
 */
class OrderIncrementIdChecker
{
    /**
     * @var ResourceModel\Order
     */
    private $resourceModel;

    /**
     * @param ResourceModel\Order $resourceModel
     */
    public function __construct(ResourceModel\Order $resourceModel)
    {
        $this->resourceModel = $resourceModel;
    }

    /**
     * Check if order increment ID is already used.
     *
     * Method can be used to avoid collisions of order IDs.
     *
     * @param string|int $orderIncrementId
     * @return bool
     */
    public function isIncrementIdUsed($orderIncrementId): bool
    {
        /** @var  \Magento\Framework\DB\Adapter\AdapterInterface $adapter */
        $adapter = $this->resourceModel->getConnection();
        $bind = [':increment_id' => $orderIncrementId];
        /** @var \Magento\Framework\DB\Select $select */
        $select = $adapter->select();
        $select->from($this->resourceModel->getMainTable(), $this->resourceModel->getIdFieldName())
            ->where('increment_id = :increment_id');
        $entity_id = $adapter->fetchOne($select, $bind);
        if ($entity_id > 0) {
            return true;
        }

        return false;
    }
}
