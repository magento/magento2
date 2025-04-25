<?php
/************************************************************************
 *
 * Copyright 2025 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\User\Controller\Adminhtml\Auth;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\User\Controller\Adminhtml\Auth;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Backend\Helper\Data;
use Magento\User\Model\UserFactory;
use Magento\User\Helper\ForceSignIn;

class ResetPasswordPost extends Auth implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var Data
     */
    private $backendDataHelper;

    /**
     * @var ForceSignIn
     */
    private ForceSignIn $forceSignIn;

    /**
     * @param Context $context
     * @param UserFactory $userFactory
     * @param Data|null $backendDataHelper
     * @param ForceSignIn|null $forceSignIn
     */
    public function __construct(
        Context $context,
        UserFactory $userFactory,
        ?Data $backendDataHelper = null,
        ?ForceSignIn $forceSignIn = null
    ) {
        parent::__construct($context, $userFactory);
        $this->backendDataHelper = $backendDataHelper ?: ObjectManager::getInstance()->get(Data::class);
        $this->forceSignIn = $forceSignIn ?: ObjectManager::getInstance()->get(ForceSignIn::class);
    }

    /**
     * Reset forgotten password
     *
     * Used to handle data received from reset forgotten password form
     *
     * @return void
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
                $this->backendDataHelper->getHomePageUrl()
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
                $this->forceSignIn->updateAdminSessionStatus($userId);
                $this->messageManager->addSuccess(__('You updated your password.'));
                $this->getResponse()->setRedirect(
                    $this->backendDataHelper->getHomePageUrl()
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
