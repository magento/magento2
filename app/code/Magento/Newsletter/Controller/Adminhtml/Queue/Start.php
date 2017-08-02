<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Adminhtml\Queue;

/**
 * Class \Magento\Newsletter\Controller\Adminhtml\Queue\Start
 *
 * @since 2.0.0
 */
class Start extends \Magento\Newsletter\Controller\Adminhtml\Queue
{
    /**
     * Start Newsletter queue
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $queue = $this->_objectManager->create(
            \Magento\Newsletter\Model\Queue::class
        )->load(
            $this->getRequest()->getParam('id')
        );
        if ($queue->getId()) {
            if (!in_array(
                $queue->getQueueStatus(),
                [\Magento\Newsletter\Model\Queue::STATUS_NEVER, \Magento\Newsletter\Model\Queue::STATUS_PAUSE]
            )
            ) {
                $this->_redirect('*/*');
                return;
            }

            $queue->setQueueStartAt(
                $this->_objectManager->get(\Magento\Framework\Stdlib\DateTime\DateTime::class)->gmtDate()
            )->setQueueStatus(
                \Magento\Newsletter\Model\Queue::STATUS_SENDING
            )->save();
        }

        $this->_redirect('*/*');
    }
}
