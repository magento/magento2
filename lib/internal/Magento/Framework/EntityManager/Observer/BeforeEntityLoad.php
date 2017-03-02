<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
