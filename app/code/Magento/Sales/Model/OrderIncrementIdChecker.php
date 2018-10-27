<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
declare(strict_types=1);
=======
>>>>>>> upstream/2.2-develop

namespace Magento\Sales\Model;

/**
<<<<<<< HEAD
 * This class uses for checking if reserved order id was already used for some order.
=======
 * This class uses for checking if reserved order id was already used for some order
>>>>>>> upstream/2.2-develop
 */
class OrderIncrementIdChecker
{
    /**
<<<<<<< HEAD
     * @var ResourceModel\Order
=======
     * @var \Magento\Sales\Model\ResourceModel\Order
>>>>>>> upstream/2.2-develop
     */
    private $resourceModel;

    /**
<<<<<<< HEAD
=======
     * OrderIncrementIdChecker constructor.
>>>>>>> upstream/2.2-develop
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
     * @param string|int $orderIncrementId
     * @return bool
     */
    public function isIncrementIdUsed($orderIncrementId): bool
=======
     * @param int $orderIncrementId
     * @return bool
     */
    public function isIncrementIdUsed($orderIncrementId)
>>>>>>> upstream/2.2-develop
    {
        /** @var  \Magento\Framework\DB\Adapter\AdapterInterface $adapter */
        $adapter = $this->resourceModel->getConnection();
        $bind = [':increment_id' => $orderIncrementId];
        /** @var \Magento\Framework\DB\Select $select */
        $select = $adapter->select();
<<<<<<< HEAD
        $select->from($this->resourceModel->getMainTable(), $this->resourceModel->getIdFieldName())
            ->where('increment_id = :increment_id');
=======
        $select->from($this->resourceModel->getMainTable(), 'entity_id')->where('increment_id = :increment_id');
>>>>>>> upstream/2.2-develop
        $entity_id = $adapter->fetchOne($select, $bind);
        if ($entity_id > 0) {
            return true;
        }

        return false;
    }
}
