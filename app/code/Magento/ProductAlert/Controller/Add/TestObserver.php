<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
