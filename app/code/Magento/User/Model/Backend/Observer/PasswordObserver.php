<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Model\Backend\Observer;

use Magento\Framework\Event\Observer as EventObserver;

/**
 * User backend observer model for passwords
 */
class PasswordObserver
{
    /**
     * Backend configuration interface
     *
     * @var \Magento\User\Model\Backend\Config\ObserverConfig
     */
    protected $observerConfig;

    /**
     * Authorization interface
     *
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * Admin user resource model
     *
     * @var \Magento\User\Model\Resource\User
     */
    protected $userResource;

    /**
     * Backend url interface
     *
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $url;

    /**
     * Backend session
     *
     * @var \Magento\Backend\Model\Session
     */
    protected $session;

    /**
     * Backend authorization session
     *
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * Encryption model
     *
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * Action flag
     *
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $actionFlag;

    /**
     * Message manager interface
     *
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param \Magento\User\Model\Backend\Config\ObserverConfig $observerConfig
     * @param \Magento\User\Model\Resource\User $userResource
     * @param \Magento\Backend\Model\UrlInterface $url
     * @param \Magento\Backend\Model\Session $session
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\User\Model\Backend\Config\ObserverConfig $observerConfig,
        \Magento\User\Model\Resource\User $userResource,
        \Magento\Backend\Model\UrlInterface $url,
        \Magento\Backend\Model\Session $session,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->authorization = $authorization;
        $this->observerConfig = $observerConfig;
        $this->userResource = $userResource;
        $this->url = $url;
        $this->session = $session;
        $this->authSession = $authSession;
        $this->encryptor = $encryptor;
        $this->actionFlag = $actionFlag;
        $this->messageManager = $messageManager;
    }

    /**
     * Harden admin password change.
     *
     * New password must be minimum 7 chars length and include alphanumeric characters
     * The password is compared to at least last 4 previous passwords to prevent setting them again
     *
     * @param EventObserver $observer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function checkAdminPasswordChange($observer)
    {
        /* @var $user \Magento\User\Model\User */
        $user = $observer->getEvent()->getObject();

        if ($user->getNewPassword()) {
            $password = $user->getNewPassword();
        } else {
            $password = $user->getPassword();
        }

        if ($password && !$user->getForceNewPassword() && $user->getId()) {
            if ($this->encryptor->isValidHash($password, $user->getOrigData('password'))) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Sorry, but this password has already been used. Please create another.')
                );
            }

            // check whether password was used before
            $resource = $this->userResource;
            $passwordHash = $this->encryptor->getHash($password, false);
            foreach ($resource->getOldPasswords($user) as $oldPasswordHash) {
                if ($passwordHash === $oldPasswordHash) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Sorry, but this password has already been used. Please create another.')
                    );
                }
            }
        }
    }

    /**
     * Save new admin password
     *
     * @param EventObserver $observer
     * @return void
     */
    public function trackAdminNewPassword($observer)
    {
        /* @var $user \Magento\User\Model\User */
        $user = $observer->getEvent()->getObject();
        if ($user->getId()) {
            $password = $user->getNewPassword();
            $passwordLifetime = $this->observerConfig->getAdminPasswordLifetime();
            if ($passwordLifetime && $password && !$user->getForceNewPassword()) {
                $resource = $this->userResource;
                $passwordHash = $this->encryptor->getHash($password, false);
                $resource->trackPassword($user, $passwordHash, $passwordLifetime);
                $this->messageManager->getMessages()->deleteMessageByIdentifier('magento_user_password_expired');
                $this->authSession->unsPciAdminUserIsPasswordExpired();
            }
        }
    }

    /**
     * Force admin to change password
     *
     * @param EventObserver $observer
     * @return void
     */
    public function forceAdminPasswordChange($observer)
    {
        if (!$this->observerConfig->isPasswordChangeForced()) {
            return;
        }
        if (!$this->authSession->isLoggedIn()) {
            return;
        }
        $actionList = [
            'adminhtml_system_account_index',
            'adminhtml_system_account_save',
            'adminhtml_auth_logout',
        ];
        /** @var \Magento\Framework\App\Action\Action $controller */
        $controller = $observer->getEvent()->getControllerAction();
        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = $observer->getEvent()->getRequest();

        if ($this->authSession->getPciAdminUserIsPasswordExpired()) {
            if (!in_array($request->getFullActionName(), $actionList)) {
                if ($this->authorization->isAllowed('Magento_Backend::myaccount')) {
                    $controller->getResponse()->setRedirect($this->url->getUrl('adminhtml/system_account/'));
                    $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                    $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_POST_DISPATCH, true);
                } else {
                    /*
                     * if admin password is expired and access to 'My Account' page is denied
                     * than we need to do force logout with error message
                     */
                    $this->authSession->clearStorage();
                    $this->session->clearStorage();
                    $this->messageManager->addErrorMessage(
                        __('Your password has expired; please contact your administrator.')
                    );
                    $controller->getRequest()->setDispatched(false);
                }
            }
        }
    }
}
