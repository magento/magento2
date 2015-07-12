<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Controller\Add;

use Magento\ProductAlert\Controller\Add as AddController;
use Magento\Framework\Object;

class TestObserver extends AddController
{
    /**
     * @return void
     */
    public function execute()
    {
        $object = new Object();
        /** @var \Magento\ProductAlert\Model\Observer $observer */
        $observer = $this->_objectManager->get('Magento\ProductAlert\Model\Observer');
        $observer->process($object);
    }
}
