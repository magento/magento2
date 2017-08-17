<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Adminhtml\Queue;

/**
 * Class \Magento\Newsletter\Controller\Adminhtml\Queue\Cancel
 *
 */
class Cancel extends \Magento\Newsletter\Controller\Adminhtml\Queue
{
    /**
     * Cancel Newsletter queue
     *
     * @return void
     */
    public function execute()
    {
        $queue = $this->_objectManager->get(
            \Magento\Newsletter\Model\Queue::class
        )->load(
            $this->getRequest()->getParam('id')
        );

        if (!in_array($queue->getQueueStatus(), [\Magento\Newsletter\Model\Queue::STATUS_SENDING])) {
            $this->_redirect('*/*');
            return;
        }

        $queue->setQueueStatus(\Magento\Newsletter\Model\Queue::STATUS_CANCEL);
        $queue->save();

        $this->_redirect('*/*');
    }
}
