<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class AfterEntityLoad
 */
class AfterEntityLoad implements ObserverInterface
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
            if ($entity->getResource() instanceof  AbstractDb) {
                $entity->getResource()->unserializeFields($entity);
            }
            $entity->getResource()->afterLoad($entity);
            $entity->afterLoad();
            $entity->setHasDataChanges(false);
        }
    }
}
