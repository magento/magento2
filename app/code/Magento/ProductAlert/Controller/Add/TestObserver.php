<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Controller\Add;

use Magento\ProductAlert\Controller\Add as AddController;
use Magento\Framework\DataObject;

/**
 * Class \Magento\ProductAlert\Controller\Add\TestObserver
 *
 * @since 2.0.0
 */
class TestObserver extends AddController
{
    /**
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $object = new DataObject();
        /** @var \Magento\ProductAlert\Model\Observer $observer */
        $observer = $this->_objectManager->get(\Magento\ProductAlert\Model\Observer::class);
        $observer->process($object);
    }
}
