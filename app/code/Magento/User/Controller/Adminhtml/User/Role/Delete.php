<?php
/**
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
            $this->_redirect('adminhtml/*/editrole', array('rid' => $rid));
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
