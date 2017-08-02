<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\EntityManager\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Model\AbstractModel;

/**
 * Class BeforeEntityLoad
 * @since 2.2.0
 */
class BeforeEntityLoad
{
    /**
     * Apply model before load operation
     *
     * @param Observer $observer
     * @throws \Magento\Framework\Validator\Exception
     * @return void
     * @since 2.2.0
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
