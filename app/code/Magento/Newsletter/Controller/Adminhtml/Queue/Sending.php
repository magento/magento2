<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Newsletter\Controller\Adminhtml\Queue;

class Sending extends \Magento\Newsletter\Controller\Adminhtml\Queue
{
    /**
     * Send Newsletter queue
     *
     * @return void
     */
    public function execute()
    {
        // Todo: put it somewhere in config!
        $countOfQueue = 3;
        $countOfSubscriptions = 20;

        $collection = $this->_objectManager->create(
            \Magento\Newsletter\Model\ResourceModel\Queue\Collection::class
        )->setPageSize(
            $countOfQueue
        )->setCurPage(
            1
        )->addOnlyForSendingFilter()->load();

        $collection->walk('sendPerSubscriber', [$countOfSubscriptions]);
    }
}
