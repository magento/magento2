<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\User\Role;

use Exception;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\User\Controller\Adminhtml\User\Role;
use Magento\User\Model\User as ModelUser;

/**
 * User roles delete action.
 */
class Delete extends Role implements HttpPostActionInterface
{
    /**
     * Remove role action.
     *
     * @return Redirect
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $rid = (int)$this->getRequest()->getParam('rid', false);
        /** @var ModelUser $currentUser */
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
        } catch (Exception $e) {
            $this->messageManager->addError(__('An error occurred while deleting this role.'));
        }

        return $resultRedirect->setPath("*/*/");
    }
}
