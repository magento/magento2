<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ProductAlert\Controller\Add;

class TestObserver extends \Magento\ProductAlert\Controller\Add
{
    /**
     * @return void
     */
    public function execute()
    {
        $object = new \Magento\Framework\Object();
        $observer = $this->_objectManager->get('Magento\ProductAlert\Model\Observer');
        $observer->process($object);
    }
}
