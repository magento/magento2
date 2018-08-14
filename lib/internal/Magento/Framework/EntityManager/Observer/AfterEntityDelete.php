<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Model\AbstractModel;

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
     * @return void
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
