<?php
/**
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
namespace Magento\User\Controller\Adminhtml;

/**
 * \Magento\User Auth controller
 */
class Auth extends \Magento\Backend\App\AbstractAction
{
    /**
     * User model factory
     *
     * @var \Magento\User\Model\UserFactory
     */
    protected $_userFactory;

    /**
     * Construct
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\User\Model\UserFactory $userFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\User\Model\UserFactory $userFactory
    ) {
        parent::__construct($context);
        $this->_userFactory = $userFactory;
    }

    /**
     * Forgot administrator password action
     *
     * @return void
     */
    public function forgotpasswordAction()
    {
        $email = (string) $this->getRequest()->getParam('email');
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
                            $newPassResetToken = $this->_objectManager->get('Magento\User\Helper\Data')
                                ->generateResetPasswordLinkToken();
                            $user->changeResetPasswordLinkToken($newPassResetToken);
                            $user->save();
                            $user->sendPasswordResetConfirmationEmail();
                        }
                        break;
                    }
                }
                // @codingStandardsIgnoreStart
                $this->messageManager
                    ->addSuccess(__('If there is an account associated with %1 you will receive an email with a link to reset your password.', $this->_objectManager->get('Magento\Escaper')->escapeHtml($email)));
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

    /**
     * Display reset forgotten password form
     *
     * User is redirected on this action when he clicks on the corresponding link in password reset confirmation email
     *
     * @return void
     */
    public function resetPasswordAction()
    {
        $passwordResetToken = (string) $this->getRequest()->getQuery('token');
        $userId = (int) $this->getRequest()->getQuery('id');
        try {
            $this->_validateResetPasswordLinkToken($userId, $passwordResetToken);

            $this->_view->loadLayout();

            $content = $this->_view->getLayout()->getBlock('content');
            if ($content) {
                $content->setData('user_id', $userId)
                    ->setData('reset_password_link_token', $passwordResetToken);
            }

            $this->_view->renderLayout();
        } catch (\Exception $exception) {
            $this->messageManager->addError(
                __('Your password reset link has expired.')
            );
            $this->_redirect('adminhtml/auth/forgotpassword', array('_nosecret' => true));
            return;
        }
    }

    /**
     * Reset forgotten password
     *
     * Used to handle data received from reset forgotten password form
     *
     * @return void
     */
    public function resetPasswordPostAction()
    {
        $passwordResetToken = (string) $this->getRequest()->getQuery('token');
        $userId = (int) $this->getRequest()->getQuery('id');
        $password = (string) $this->getRequest()->getPost('password');
        $passwordConfirmation = (string) $this->getRequest()->getPost('confirmation');

        try {
            $this->_validateResetPasswordLinkToken($userId, $passwordResetToken);
        } catch (\Exception $exception) {
            $this->messageManager->addError(
                __('Your password reset link has expired.')
            );
            $this->getResponse()->setRedirect(
                $this->_objectManager->get('Magento\Backend\Helper\Data')->getHomePageUrl()
            );
            return;
        }

        /** @var $user \Magento\User\Model\User */
        $user = $this->_userFactory->create()->load($userId);
        if ($password !== '') {
            $user->setPassword($password);
        }
        if ($passwordConfirmation !== '') {
            $user->setPasswordConfirmation($passwordConfirmation);
        }
        // Empty current reset password token i.e. invalidate it
        $user->setRpToken(null);
        $user->setRpTokenCreatedAt(null);
        try {
            $user->save();
            $this->messageManager->addSuccess(
                __('Your password has been updated.')
            );
            $this->getResponse()->setRedirect(
                $this->_objectManager->get('Magento\Backend\Helper\Data')->getHomePageUrl()
            );
        } catch (\Magento\Core\Exception $exception) {
            $this->messageManager->addMessages($exception->getMessages());
            $this->_redirect('adminhtml/auth/resetpassword', array(
                '_nosecret' => true,
                '_query' => array(
                    'id' => $userId,
                    'token' => $passwordResetToken
                )
            ));
        }
    }

    /**
     * Check if password reset token is valid
     *
     * @param int $userId
     * @param string $resetPasswordToken
     * @return void
     * @throws \Magento\Core\Exception
     */
    protected function _validateResetPasswordLinkToken($userId, $resetPasswordToken)
    {
        if (!is_int($userId)
            || !is_string($resetPasswordToken)
            || empty($resetPasswordToken)
            || empty($userId)
            || $userId < 0
        ) {
            throw new \Magento\Core\Exception(__('Please correct the password reset token.'));
        }

        /** @var $user \Magento\User\Model\User */
        $user = $this->_userFactory->create()->load($userId);
        if (!$user->getId()) {
            throw new \Magento\Core\Exception(__('Please specify the correct account and try again.'));
        }

        $userToken = $user->getRpToken();
        if (strcmp($userToken, $resetPasswordToken) != 0 || $user->isResetPasswordLinkTokenExpired()) {
            throw new \Magento\Core\Exception(__('Your password reset link has expired.'));
        }
    }

    /**
     * Check if user has permissions to access this controller
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
    }
}
