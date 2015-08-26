<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PasswordManagement\Model\Backend;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\Encryption\Encryptor;

/**
 * PasswordManagement backend observer model
 *
 * Implements hashes upgrading
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Observer
{
    /**
     * Authorization interface
     *
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * Backend configuration interface
     *
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $_backendConfig;

    /**
     * Admin user resource model
     *
     * @var \Magento\PasswordManagement\Model\Resource\Admin\User
     */
    protected $_userResource;

    /**
     * Backend url interface
     *
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_url;

    /**
     * Backend session
     *
     * @var \Magento\Backend\Model\Session
     */
    protected $_session;

    /**
     * Backend authorization session
     *
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $_authSession;

    /**
     * Factory class for user model
     *
     * @var \Magento\User\Model\UserFactory
     */
    protected $_userFactory;

    /**
     * PasswordManagement encryption model
     *
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;

    /**
     * Action flag
     *
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_actionFlag;

    /**
     * Message manager interface
     *
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param \Magento\Backend\App\ConfigInterface $backendConfig
     * @param \Magento\PasswordManagement\Model\Resource\Admin\User $userResource
     * @param \Magento\Backend\Model\UrlInterface $url
     * @param \Magento\Backend\Model\Session $session
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\App\ActionFlag $actionFlag
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\Backend\App\ConfigInterface $backendConfig,
        \Magento\PasswordManagement\Model\Resource\Admin\User $userResource,
        \Magento\Backend\Model\UrlInterface $url,
        \Magento\Backend\Model\Session $session,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_authorization = $authorization;
        $this->_backendConfig = $backendConfig;
        $this->_userResource = $userResource;
        $this->_url = $url;
        $this->_session = $session;
        $this->_authSession = $authSession;
        $this->_userFactory = $userFactory;
        $this->_encryptor = $encryptor;
        $this->_actionFlag = $actionFlag;
        $this->messageManager = $messageManager;
    }

    /**
     * Admin locking and password hashing upgrade logic implementation
     *
     * @param EventObserver $observer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function adminAuthenticate($observer)
    {
        $password = $observer->getEvent()->getPassword();
        $user = $observer->getEvent()->getUser();
        $resource = $this->_userResource;
        $authResult = $observer->getEvent()->getResult();

        // update locking information regardless whether user locked or not
        if (!$authResult && $user->getId()) {
            $now = new \DateTime();
            $lockThreshold = $this->getAdminLockThreshold();
            $maxFailures = (int)$this->_backendConfig->getValue('admin/security/lockout_failures');
            if (!($lockThreshold && $maxFailures)) {
                return;
            }
            $failuresNum = (int)$user->getFailuresNum() + 1;
            $firstFailureDate = $user->getFirstFailure();
            if ($firstFailureDate) {
                $firstFailureDate = new \DateTime($firstFailureDate);
            }

            $updateFirstFailureDate = false;
            $updateLockExpires = false;
            $lockThresholdInterval = new \DateInterval('PT' . $lockThreshold . 'S');
            // set first failure date when this is first failure or last first failure expired
            if (1 === $failuresNum || !$firstFailureDate || $now->diff($firstFailureDate) > $lockThresholdInterval) {
                $updateFirstFailureDate = $now;
                // otherwise lock user
            } elseif ($failuresNum >= $maxFailures) {
                $updateLockExpires = $now->add($lockThresholdInterval);
            }
            $resource->updateFailure($user, $updateLockExpires, $updateFirstFailureDate);
        }

        // check whether user is locked
        $lockExpires = $user->getLockExpires();
        if ($lockExpires) {
            $lockExpires = new \DateTime($lockExpires);
            if ($lockExpires > new \DateTime()) {
                throw new UserLockedException(
                    __('You did not sign in correctly or your account is temporarily disabled.')
                );
            }
        }

        if (!$authResult) {
            return;
        }

        $resource->unlock($user->getId());

        /**
         * Check whether the latest password is expired
         * Side-effect can be when passwords were changed with different lifetime configuration settings
         */
        $latestPassword = $this->_userResource->getLatestPassword($user->getId());
        if ($latestPassword) {
            if ($this->_isLatestPasswordExpired($latestPassword)) {
                if ($this->isPasswordChangeForced()) {
                    $message = __('It\'s time to change your password.');
                } else {
                    $myAccountUrl = $this->_url->getUrl('adminhtml/system_account/');
                    $message = __('It\'s time to <a href="%1">change your password</a>.', $myAccountUrl);
                }
                $this->messageManager->addNotice($message);
                $message = $this->messageManager->getMessages()->getLastAddedMessage();
                if ($message) {
                    $message->setIdentifier('magento_pm_password_expired')->setIsSticky(true);
                    $this->_authSession->setPciAdminUserIsPasswordExpired(true);
                }
            }
        }

        // upgrade admin password
        $isValidHash = $this->_encryptor->validateHashByVersion(
            $password,
            $user->getPassword(),
            Encryptor::HASH_VERSION_LATEST
        );
        if (!$isValidHash) {
            $this->_userFactory->create()
                ->load($user->getId())
                ->setNewPassword($password)
                ->setForceNewPassword(true)
                ->save();
        }
    }

    /**
     * Check if latest password is expired
     *
     * @param array $latestPassword
     * @return bool
     */
    protected function _isLatestPasswordExpired($latestPassword)
    {
        if (!isset($latestPassword['expires'])) {
            return false;
        }

        if ($this->getAdminPasswordLifetime() == 0) {
            return false;
        }

        return (int)$latestPassword['expires'] < time();
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
            if ($this->_encryptor->validateHash($password, $user->getOrigData('password'))) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Sorry, but this password has already been used. Please create another.')
                );
            }

            // check whether password was used before
            $resource = $this->_userResource;
            $passwordHash = $this->_encryptor->getHash($password, false);
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
            $passwordLifetime = $this->getAdminPasswordLifetime();
            if ($passwordLifetime && $password && !$user->getForceNewPassword()) {
                $resource = $this->_userResource;
                $passwordHash = $this->_encryptor->getHash($password, false);
                $resource->trackPassword($user, $passwordHash, $passwordLifetime);
                $this->messageManager->getMessages()->deleteMessageByIdentifier('magento_pm_password_expired');
                $this->_authSession->unsPciAdminUserIsPasswordExpired();
            }
        }
    }

    /**
     * Get admin lock threshold from configuration
     *
     * @return int
     */
    public function getAdminLockThreshold()
    {
        return 60 * (int)$this->_backendConfig->getValue('admin/security/lockout_threshold');
    }

    /**
     * Get admin password lifetime
     *
     * @return int
     */
    public function getAdminPasswordLifetime()
    {
        return 86400 * (int)$this->_backendConfig->getValue('admin/security/password_lifetime');
    }

    /**
     * Force admin to change password
     *
     * @param EventObserver $observer
     * @return void
     */
    public function forceAdminPasswordChange($observer)
    {
        if (!$this->isPasswordChangeForced()) {
            return;
        }
        $session = $this->_authSession;
        if (!$session->isLoggedIn()) {
            return;
        }
        $actionList = [
            'adminhtml_system_account_index',
            'adminhtml_system_account_save',
            'adminhtml_auth_logout',
        ];
        $controller = $observer->getEvent()->getControllerAction();
        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = $observer->getEvent()->getRequest();
        if ($this->_authSession->getPciAdminUserIsPasswordExpired()) {
            if (!in_array($request->getFullActionName(), $actionList)) {
                if ($this->_authorization->isAllowed('Magento_Backend::myaccount')) {
                    $controller->getResponse()->setRedirect($this->_url->getUrl('adminhtml/system_account/'));
                    $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                    $this->_actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_POST_DISPATCH, true);
                } else {
                    /*
                     * if admin password is expired and access to 'My Account' page is denied
                     * than we need to do force logout with error message
                     */
                    $this->_authSession->clearStorage();
                    $this->_session->clearStorage();
                    $this->messageManager->addError(
                        __('Your password has expired; please contact your administrator.')
                    );
                    $controller->getRequest()->setDispatched(false);
                }
            }
        }
    }

    /**
     * Check whether password change is forced
     *
     * @return bool
     */
    public function isPasswordChangeForced()
    {
        return (bool)(int)$this->_backendConfig->getValue('admin/security/password_is_forced');
    }
}
