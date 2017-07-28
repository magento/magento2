<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Adminhtml\Queue;

/**
 * Class \Magento\Newsletter\Controller\Adminhtml\Queue\Resume
 *
 * @since 2.0.0
 */
class Resume extends \Magento\Newsletter\Controller\Adminhtml\Queue
{
    /**
     * Resume Newsletter queue
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $queue = $this->_objectManager->get(
            \Magento\Newsletter\Model\Queue::class
        )->load(
            $this->getRequest()->getParam('id')
        );

        if (!in_array($queue->getQueueStatus(), [\Magento\Newsletter\Model\Queue::STATUS_PAUSE])) {
            $this->_redirect('*/*');
            return;
        }

        $queue->setQueueStatus(\Magento\Newsletter\Model\Queue::STATUS_SENDING);
        $queue->save();

        $this->_redirect('*/*');
    }
}
