<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class AfterEntityDelete
 */
class AfterEntityDelete implements ObserverInterface
{
    /**
     * Apply model delete operation
     *
     * @param Observer $observer
     * @throws \Magento\Framework\Validator\Exception
     */
    public function execute(Observer $observer)
    {
        $entity = $observer->getEvent()->getEntity();
        if ($entity instanceof AbstractModel) {
            $entity->getResource()->afterDelete($entity);
            $entity->isDeleted(true);
            $entity->afterDelete();
            $entity->getResource()->addCommitCallback([$entity, 'afterDeleteCommit']);
        }
    }
}
