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
namespace Magento\User\Controller\Adminhtml\User;

class Delete extends \Magento\User\Controller\Adminhtml\User
{
    /**
     * @return void
     */
    public function execute()
    {
        $currentUser = $this->_objectManager->get('Magento\Backend\Model\Auth\Session')->getUser();

        if ($userId = $this->getRequest()->getParam('user_id')) {
            if ($currentUser->getId() == $userId) {
                $this->messageManager->addError(__('You cannot delete your own account.'));
                $this->_redirect('adminhtml/*/edit', array('user_id' => $userId));
                return;
            }
            try {
                /** @var \Magento\User\Model\User $model */
                $model = $this->_userFactory->create();
                $model->setId($userId);
                $model->delete();
                $this->messageManager->addSuccess(__('You deleted the user.'));
                $this->_redirect('adminhtml/*/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('adminhtml/*/edit', array('user_id' => $this->getRequest()->getParam('user_id')));
                return;
            }
        }
        $this->messageManager->addError(__('We can\'t find a user to delete.'));
        $this->_redirect('adminhtml/*/');
    }
}
