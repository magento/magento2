<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Adminhtml\Subscriber;

use Magento\Framework\Exception\NotFoundException;

class MassUnsubscribe extends \Magento\Newsletter\Controller\Adminhtml\Subscriber
{
    /**
     * Unsubscribe one or more subscribers action
     *
     * @return void
     * @throws NotFoundException
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            throw new NotFoundException(__('Page not found.'));
        }

        $subscribersIds = $this->getRequest()->getParam('subscriber');
        if (!is_array($subscribersIds)) {
            $this->messageManager->addError(__('Please select one or more subscribers.'));
        } else {
            try {
                foreach ($subscribersIds as $subscriberId) {
                    $subscriber = $this->_objectManager->create(
                        \Magento\Newsletter\Model\Subscriber::class
                    )->load(
                        $subscriberId
                    );
                    $subscriber->unsubscribe();
                }
                $this->messageManager->addSuccess(__('A total of %1 record(s) were updated.', count($subscribersIds)));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }
}
