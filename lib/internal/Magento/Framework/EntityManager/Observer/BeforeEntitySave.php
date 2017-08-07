<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class BeforeEntitySave
 * @since 2.1.0
 */
class BeforeEntitySave implements ObserverInterface
{
    /**
     * Apply model save operation
     *
     * @param Observer $observer
     * @throws \Magento\Framework\Validator\Exception
     * @return void
     * @since 2.1.0
     */
    public function execute(Observer $observer)
    {
        $entity = $observer->getEvent()->getEntity();
        if ($entity instanceof AbstractModel) {
            if ($entity->getResource() instanceof  AbstractDb) {
                $entity = $entity->getResource()->serializeFields($entity);
            }
            $entity->validateBeforeSave();
            $entity->beforeSave();
            $entity->setParentId((int)$entity->getParentId());
            $entity->getResource()->beforeSave($entity);
        }
    }
}
