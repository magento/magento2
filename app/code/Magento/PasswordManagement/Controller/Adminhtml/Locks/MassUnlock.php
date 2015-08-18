<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PasswordManagement\Controller\Adminhtml\Locks;

use Magento\Framework\Controller\ResultFactory;

class MassUnlock extends \Magento\PasswordManagement\Controller\Adminhtml\Locks
{
    /**
     * Unlock specified users
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            // unlock users
            $userIds = $this->getRequest()->getPost('unlock');
            if ($userIds && is_array($userIds)) {
                $affectedUsers = $this->_objectManager->get('Magento\PasswordManagement\Model\Resource\Admin\User')->unlock($userIds);
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
