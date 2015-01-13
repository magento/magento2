<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\User\Role;

class Delete extends \Magento\User\Controller\Adminhtml\User\Role
{
    /**
     * Remove role action
     *
     * @return void
     */
    public function execute()
    {
        $rid = $this->getRequest()->getParam('rid', false);
        /** @var \Magento\User\Model\User $currentUser */
        $currentUser = $this->_userFactory->create()->setId($this->_authSession->getUser()->getId());

        if (in_array($rid, $currentUser->getRoles())) {
            $this->messageManager->addError(__('You cannot delete self-assigned roles.'));
            $this->_redirect('adminhtml/*/editrole', ['rid' => $rid]);
            return;
        }

        try {
            $this->_initRole()->delete();
            $this->messageManager->addSuccess(__('You deleted the role.'));
        } catch (\Exception $e) {
            $this->messageManager->addError(__('An error occurred while deleting this role.'));
        }

        $this->_redirect("*/*/");
    }
}
