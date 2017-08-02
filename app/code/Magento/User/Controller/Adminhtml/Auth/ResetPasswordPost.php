<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\User\Controller\Adminhtml\Auth;

/**
 * Class \Magento\User\Controller\Adminhtml\Auth\ResetPasswordPost
 *
 * @since 2.0.0
 */
class ResetPasswordPost extends \Magento\User\Controller\Adminhtml\Auth
{
    /**
     * Reset forgotten password
     *
     * Used to handle data received from reset forgotten password form
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        $passwordResetToken = (string)$this->getRequest()->getQuery('token');
        $userId = (int)$this->getRequest()->getQuery('id');
        $password = (string)$this->getRequest()->getPost('password');
        $passwordConfirmation = (string)$this->getRequest()->getPost('confirmation');

        try {
            $this->_validateResetPasswordLinkToken($userId, $passwordResetToken);
        } catch (\Exception $exception) {
            $this->messageManager->addError(__('Your password reset link has expired.'));
            $this->getResponse()->setRedirect(
                $this->_objectManager->get(\Magento\Backend\Helper\Data::class)->getHomePageUrl()
            );
            return;
        }

        /** @var $user \Magento\User\Model\User */
        $user = $this->_userFactory->create()->load($userId);
        $user->setPassword($password);
        $user->setPasswordConfirmation($passwordConfirmation);
        // Empty current reset password token i.e. invalidate it
        $user->setRpToken(null);
        $user->setRpTokenCreatedAt(null);
        try {
            $errors = $user->validate();
            if ($errors !== true && !empty($errors)) {
                foreach ($errors as $error) {
                    $this->messageManager->addError($error);
                    $this->_redirect(
                        'adminhtml/auth/resetpassword',
                        ['_nosecret' => true, '_query' => ['id' => $userId, 'token' => $passwordResetToken]]
                    );
                }
            } else {
                $user->save();
                $this->messageManager->addSuccess(__('You updated your password.'));
                $this->getResponse()->setRedirect(
                    $this->_objectManager->get(\Magento\Backend\Helper\Data::class)->getHomePageUrl()
                );
            }
        } catch (\Magento\Framework\Validator\Exception $exception) {
            $this->messageManager->addMessages($exception->getMessages());
            $this->_redirect(
                'adminhtml/auth/resetpassword',
                ['_nosecret' => true, '_query' => ['id' => $userId, 'token' => $passwordResetToken]]
            );
        }
    }
}
