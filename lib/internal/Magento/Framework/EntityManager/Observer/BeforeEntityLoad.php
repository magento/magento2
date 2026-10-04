<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\EntityManager\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Model\AbstractModel;

/**
 * Class BeforeEntityLoad
 */
class BeforeEntityLoad
{
    /**
     * Apply model before load operation
     *
     * @param Observer $observer
     * @throws \Magento\Framework\Validator\Exception
     * @return void
     */
    public function execute(Observer $observer)
    {
        $identifier = $observer->getEvent()->getIdentifier();
        $entity = $observer->getEvent()->getEntity();
        if ($entity instanceof AbstractModel) {
            $entity->beforeLoad($identifier);
        }
    }
}
