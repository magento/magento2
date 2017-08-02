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
 * Class BeforeEntityDelete
 * @since 2.1.0
 */
class BeforeEntityDelete implements ObserverInterface
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
            $entity->beforeDelete();
            $entity->getResource()->beforeDelete($entity);
        }
    }
}
