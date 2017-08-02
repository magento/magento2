<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\Locks;

use Magento\Framework\Controller\ResultFactory;

/**
 * Mass Unlock Controller
 * @since 2.0.0
 */
class MassUnlock extends \Magento\User\Controller\Adminhtml\Locks
{
    /**
     * Unlock specified users
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @since 2.0.0
     */
    public function execute()
    {
        try {
            // unlock users
            $userIds = $this->getRequest()->getPost('unlock');
            if ($userIds && is_array($userIds)) {
                $affectedUsers = $this->_objectManager
                    ->get(\Magento\User\Model\ResourceModel\User::class)
                    ->unlock($userIds);
                $this->getMessageManager()->addSuccess(__('Unlocked %1 user(s).', $affectedUsers));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('adminhtml/*/');
    }
}
