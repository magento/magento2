<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        $countOfSubscritions = 20;

        $collection = $this->_objectManager->create(
            'Magento\Newsletter\Model\ResourceModel\Queue\Collection'
        )->setPageSize(
            $countOfQueue
        )->setCurPage(
            1
        )->addOnlyForSendingFilter()->load();

        $collection->walk('sendPerSubscriber', [$countOfSubscritions]);
    }
}
