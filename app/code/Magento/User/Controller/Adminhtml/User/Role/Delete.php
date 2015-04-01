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
     * @return \Magento\Backend\Model\View\Result\Redirect|void
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

        $this->_initRole()->delete();
        $this->messageManager->addSuccess(__('You deleted the role.'));
        return $this->getDefaultResult();
    }

    /**
     * @inheritdoc
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function getDefaultResult()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath("*/*/");
    }
}
