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
use Magento\Eav\Model\Entity\AbstractEntity as EavResource;

/**
 * Class BeforeEntitySave
 */
class BeforeEntitySave implements ObserverInterface
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
            $resource = $entity->getResource();
            if ($resource instanceof  AbstractDb) {
                $entity = $resource->serializeFields($entity);
            }
            $entity->validateBeforeSave();
            $entity->beforeSave();
            $entity->setParentId((int)$entity->getParentId());
            if ($resource instanceof EavResource) {
                //Because another item might have been loaded/saved before
                //with different set of attributes.
                $resource->loadAllAttributes($entity);
            }
            $entity->getResource()->beforeSave($entity);
        }
    }
}
