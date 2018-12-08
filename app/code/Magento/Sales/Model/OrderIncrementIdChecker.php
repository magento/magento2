<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3

namespace Magento\Sales\Model;

/**
<<<<<<< HEAD
 * This class uses for checking if reserved order id was already used for some order
=======
 * This class uses for checking if reserved order id was already used for some order.
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
 */
class OrderIncrementIdChecker
{
    /**
<<<<<<< HEAD
     * @var \Magento\Sales\Model\ResourceModel\Order
=======
     * @var ResourceModel\Order
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
     */
    private $resourceModel;

    /**
<<<<<<< HEAD
     * OrderIncrementIdChecker constructor.
=======
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
<<<<<<< HEAD
     * @param int $orderIncrementId
     * @return bool
     */
    public function isIncrementIdUsed($orderIncrementId)
=======
     * @param string|int $orderIncrementId
     * @return bool
     */
    public function isIncrementIdUsed($orderIncrementId): bool
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
    {
        /** @var  \Magento\Framework\DB\Adapter\AdapterInterface $adapter */
        $adapter = $this->resourceModel->getConnection();
        $bind = [':increment_id' => $orderIncrementId];
        /** @var \Magento\Framework\DB\Select $select */
        $select = $adapter->select();
<<<<<<< HEAD
        $select->from($this->resourceModel->getMainTable(), 'entity_id')->where('increment_id = :increment_id');
=======
        $select->from($this->resourceModel->getMainTable(), $this->resourceModel->getIdFieldName())
            ->where('increment_id = :increment_id');
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        $entity_id = $adapter->fetchOne($select, $bind);
        if ($entity_id > 0) {
            return true;
        }

        return false;
    }
}
