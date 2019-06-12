<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc

namespace Magento\Sales\Model;

/**
<<<<<<< HEAD
 * This class uses for checking if reserved order id was already used for some order
=======
 * This class uses for checking if reserved order id was already used for some order.
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
 */
class OrderIncrementIdChecker
{
    /**
<<<<<<< HEAD
     * @var \Magento\Sales\Model\ResourceModel\Order
=======
     * @var ResourceModel\Order
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     */
    private $resourceModel;

    /**
<<<<<<< HEAD
     * OrderIncrementIdChecker constructor.
=======
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        $entity_id = $adapter->fetchOne($select, $bind);
        if ($entity_id > 0) {
            return true;
        }

        return false;
    }
}
