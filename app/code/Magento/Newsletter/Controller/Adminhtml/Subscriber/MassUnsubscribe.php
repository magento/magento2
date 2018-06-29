<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Controller\Adminhtml\Subscriber;

class MassUnsubscribe extends \Magento\Newsletter\Controller\Adminhtml\Subscriber
{
    /**
     * Unsubscribe one or more subscribers action
     *
     * @return void
     */
    public function execute()
    {
        $subscribersIds = $this->getRequest()->getParam('subscriber');
        if (!is_array($subscribersIds)) {
            $this->messageManager->addErrorMessage(__('Please select one or more subscribers.'));
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
                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) were updated.',
                       count($subscribersIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        $this->_redirect('*/*/index');
    }
}
