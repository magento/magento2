<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\Auth;

class Forgotpassword extends \Magento\User\Controller\Adminhtml\Auth
{
    /**
     * Forgot administrator password action
     *
     * @return void
     */
    public function execute()
    {
        $email = (string)$this->getRequest()->getParam('email');
        $params = $this->getRequest()->getParams();

        if (!empty($email) && !empty($params)) {
            // Validate received data to be an email address
            if (\Zend_Validate::is($email, 'EmailAddress')) {
                $collection = $this->_objectManager->get('Magento\User\Model\ResourceModel\User\Collection');
                /** @var $collection \Magento\User\Model\ResourceModel\User\Collection */
                $collection->addFieldToFilter('email', $email);
                $collection->load(false);

                if ($collection->getSize() > 0) {
                    foreach ($collection as $item) {
                        /** @var \Magento\User\Model\User $user */
                        $user = $this->_userFactory->create()->load($item->getId());
                        if ($user->getId()) {
                            $newPassResetToken = $this->_objectManager->get(
                                'Magento\User\Helper\Data'
                            )->generateResetPasswordLinkToken();
                            $user->changeResetPasswordLinkToken($newPassResetToken);
                            $user->save();
                            $user->sendPasswordResetConfirmationEmail();
                        }
                        break;
                    }
                }
                // @codingStandardsIgnoreStart
                $this->messageManager->addSuccess(__('We\'ll email you a link to reset your password.'));
                // @codingStandardsIgnoreEnd
                $this->getResponse()->setRedirect(
                    $this->_objectManager->get('Magento\Backend\Helper\Data')->getHomePageUrl()
                );
                return;
            } else {
                $this->messageManager->addError(__('Please correct this email address:'));
            }
        } elseif (!empty($params)) {
            $this->messageManager->addError(__('Please enter an email address.'));
        }
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
