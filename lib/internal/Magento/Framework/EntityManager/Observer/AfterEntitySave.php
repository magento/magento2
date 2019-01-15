<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class AfterEntitySave
 */
class AfterEntitySave implements ObserverInterface
{
    /**
     * Apply model save operation
     *
     * @param Observer $observer
     * @throws \Magento\Framework\Validator\Exception
     * @return void
     */
    public function execute(Observer $observer)
    {
        $entity = $observer->getEvent()->getEntity();
        if ($entity instanceof AbstractModel) {
            if (method_exists($entity->getResource(), 'loadAllAttributes')) {
                $entity->getResource()->loadAllAttributes($entity);
            }
            $entity->getResource()->afterSave($entity);
            $entity->afterSave();
            $entity->getResource()->addCommitCallback([$entity, 'afterCommitCallback']);
            if ($entity->getResource() instanceof  AbstractDb) {
                $entity->getResource()->unserializeFields($entity);
            }
            $entity->setHasDataChanges(false);
        }
    }
}
