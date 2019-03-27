<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\User\Role;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NotFoundException;

/**
 * User roles delete action.
 */
class Delete extends \Magento\User\Controller\Adminhtml\User\Role
{
    /**
     * Remove role action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws NotFoundException
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            throw new NotFoundException(__('Page not found'));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $rid = (int)$this->getRequest()->getParam('rid', false);
        /** @var \Magento\User\Model\User $currentUser */
        $currentUser = $this->_userFactory->create()->setId($this->_authSession->getUser()->getId());

        if (in_array($rid, $currentUser->getRoles())) {
            $this->messageManager->addError(__('You cannot delete self-assigned roles.'));

            return $resultRedirect->setPath('adminhtml/*/editrole', ['rid' => $rid]);
        }
        $role = $this->_initRole();
        if (!$role->getId()) {
            $this->messageManager->addError(__('We can\'t find a role to delete.'));

            return $resultRedirect->setPath("*/*/");
        }

        try {
            $role->delete();
            $this->messageManager->addSuccess(__('You deleted the role.'));
        } catch (\Exception $e) {
            $this->messageManager->addError(__('An error occurred while deleting this role.'));
        }

        return $resultRedirect->setPath("*/*/");
    }
}
