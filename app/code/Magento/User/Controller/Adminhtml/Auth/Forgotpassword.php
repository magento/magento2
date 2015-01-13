<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
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
                $collection = $this->_objectManager->get('Magento\User\Model\Resource\User\Collection');
                /** @var $collection \Magento\User\Model\Resource\User\Collection */
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
                $this->messageManager->addSuccess(
                    __(
                        'If there is an account associated with %1 you will receive an email with a link to reset your password.',
                        $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($email)
                    )
                );
                // @codingStandardsIgnoreEnd
                $this->getResponse()->setRedirect(
                    $this->_objectManager->get('Magento\Backend\Helper\Data')->getHomePageUrl()
                );
                return;
            } else {
                $this->messageManager->addError(__('Please correct this email address:'));
            }
        } elseif (!empty($params)) {
            $this->messageManager->addError(__('The email address is empty.'));
        }
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
