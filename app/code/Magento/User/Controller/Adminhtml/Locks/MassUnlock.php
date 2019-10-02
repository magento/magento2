<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\Locks;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\User\Controller\Adminhtml\Locks;

/**
 * Mass Unlock Controller
 */
class MassUnlock extends Locks implements HttpPostActionInterface
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
                $affectedUsers = $this->_objectManager
                    ->get(\Magento\User\Model\ResourceModel\User::class)
                    ->unlock($userIds);
                $this->getMessageManager()->addSuccessMessage(__('Unlocked %1 user(s).', $affectedUsers));
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('adminhtml/*/');
    }
}
