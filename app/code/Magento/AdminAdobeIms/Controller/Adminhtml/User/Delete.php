<?php
// phpcs:ignoreFile
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdminAdobeIms\Controller\Adminhtml\User;

use Magento\Backend\Model\Auth\Session;
use Magento\User\Block\User\Edit\Tab\Main as UserEdit;
use Magento\User\Controller\Adminhtml\User as UserController;
use Magento\User\Model\User;

/**
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Delete extends UserController
{
    /**
     * @return void
     */
    public function execute()
    {
        /** @var User */
        $currentUser = $this->_objectManager->get(Session::class)->getUser();
        $userId = (int)$this->getRequest()->getPost('user_id');

        if ($userId) {
            if ($currentUser->getId() === $userId) {
                $this->messageManager->addError(__('You cannot delete your own account.'));
                $this->_redirect('adminhtml/*/edit', ['user_id' => $userId]);
                return;
            }
            try {
                $currentUserPassword = (string)$this->getRequest()->getPost(UserEdit::CURRENT_USER_PASSWORD_FIELD);
                $currentUser->performIdentityCheck($currentUserPassword);
                /** @var User $model */
                $model = $this->_userFactory->create();
                $model->setId($userId);
                $model->delete();
                $this->messageManager->addSuccess(__('You deleted the user.'));
                $this->_redirect('adminhtml/*/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('adminhtml/*/edit', ['user_id' => $this->getRequest()->getParam('user_id')]);
                return;
            }
        }
        $this->messageManager->addError(__('We can\'t find a user to delete.'));
        $this->_redirect('adminhtml/*/');
    }
}
