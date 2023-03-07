<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\Locks;

use Exception;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\User\Controller\Adminhtml\Locks;
use Magento\User\Model\ResourceModel\User;

/**
 * Mass Unlock Controller
 */
class MassUnlock extends Locks
{
    /**
     * Unlock specified users
     *
     * @return Redirect
     */
    public function execute()
    {
        try {
            // unlock users
            $userIds = $this->getRequest()->getPost('unlock');
            if ($userIds && is_array($userIds)) {
                $affectedUsers = $this->_objectManager
                    ->get(User::class)
                    ->unlock($userIds);
                $this->getMessageManager()->addSuccess(__('Unlocked %1 user(s).', $affectedUsers));
            }
        } catch (Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('adminhtml/*/');
    }
}
