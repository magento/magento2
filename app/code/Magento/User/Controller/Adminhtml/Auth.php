<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
     * Check if password reset token is valid
     *
     * @param int $userId
     * @param string $resetPasswordToken
     * @return void
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _validateResetPasswordLinkToken($userId, $resetPasswordToken)
    {
        if (!is_int(
            $userId
        ) || !is_string(
            $resetPasswordToken
        ) || empty($resetPasswordToken) || empty($userId) || $userId < 0
        ) {
            throw new \Magento\Framework\Model\Exception(__('Please correct the password reset token.'));
        }

        /** @var $user \Magento\User\Model\User */
        $user = $this->_userFactory->create()->load($userId);
        if (!$user->getId()) {
            throw new \Magento\Framework\Model\Exception(__('Please specify the correct account and try again.'));
        }

        $userToken = $user->getRpToken();
        if (strcmp($userToken, $resetPasswordToken) != 0 || $user->isResetPasswordLinkTokenExpired()) {
            throw new \Magento\Framework\Model\Exception(__('Your password reset link has expired.'));
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
