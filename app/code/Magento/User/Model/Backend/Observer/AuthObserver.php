<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Model\Backend\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\Encryption\Encryptor;

/**
 * User backend observer model for authentication
 */
class AuthObserver
{
    /**
     * Backend configuration interface
     *
     * @var \Magento\User\Model\Backend\Config\ObserverConfig
     */
    protected $observerConfig;

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
     * Backend authorization session
     *
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * Factory class for user model
     *
     * @var \Magento\User\Model\UserFactory
     */
    protected $userFactory;

    /**
     * Encryption model
     *
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * Message manager interface
     *
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @param \Magento\User\Model\Backend\Config\ObserverConfig $observerConfig
     * @param \Magento\User\Model\Resource\User $userResource
     * @param \Magento\Backend\Model\UrlInterface $url
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\User\Model\UserFactory $userFactory
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\User\Model\Backend\Config\ObserverConfig $observerConfig,
        \Magento\User\Model\Resource\User $userResource,
        \Magento\Backend\Model\UrlInterface $url,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\User\Model\UserFactory $userFactory,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->observerConfig = $observerConfig;
        $this->userResource = $userResource;
        $this->url = $url;
        $this->authSession = $authSession;
        $this->userFactory = $userFactory;
        $this->encryptor = $encryptor;
        $this->messageManager = $messageManager;
    }

    /**
     * Admin locking and password hashing upgrade logic implementation
     *
     * @param EventObserver $observer
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function adminAuthenticate($observer)
    {
        $password = $observer->getEvent()->getPassword();
        $user = $observer->getEvent()->getUser();
        $authResult = $observer->getEvent()->getResult();

        if (!$authResult && $user->getId()) {
            // update locking information regardless whether user locked or not
            $this->_updateLockingInformation($user);
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

        $this->userResource->unlock($user->getId());

        $latestPassword = $this->userResource->getLatestPassword($user->getId());
        $this->_checkExpiredPassword($latestPassword);

        // upgrade admin password
        $isValidHash = $this->encryptor->isValidHashByVersion(
            $password,
            $user->getPassword(),
            Encryptor::HASH_VERSION_LATEST
        );
        if (!$isValidHash) {
            $this->userFactory->create()
                ->load($user->getId())
                ->setNewPassword($password)
                ->setForceNewPassword(true)
                ->save();
        }
    }

    /**
     * Update locking information for the user
     *
     * @param \Magento\User\Model\User $user
     * @return void
     */
    private function _updateLockingInformation($user)
    {
        $now = new \DateTime();
        $lockThreshold = $this->observerConfig->getAdminLockThreshold();
        $maxFailures = $this->observerConfig->getMaxFailures();
        if (!($lockThreshold && $maxFailures)) {
            return;
        }
        $failuresNum = (int)$user->getFailuresNum() + 1;
        /** @noinspection PhpAssignmentInConditionInspection */
        if ($firstFailureDate = $user->getFirstFailure()) {
            $firstFailureDate = new \DateTime($firstFailureDate);
        }

        $newFirstFailureDate = false;
        $updateLockExpires = false;
        $lockThreshInterval = new \DateInterval('PT' . $lockThreshold.'S');
        // set first failure date when this is first failure or last first failure expired
        if (1 === $failuresNum || !$firstFailureDate || $now->diff($firstFailureDate) > $lockThreshInterval) {
            $newFirstFailureDate = $now;
            // otherwise lock user
        } elseif ($failuresNum >= $maxFailures) {
            $updateLockExpires = $now->add($lockThreshInterval);
        }
        $this->userResource->updateFailure($user, $updateLockExpires, $newFirstFailureDate);
    }

    /**
     * Check whether the latest password is expired
     * Side-effect can be when passwords were changed with different lifetime configuration settings
     *
     * @param array $latestPassword
     * @return void
     */
    private function _checkExpiredPassword($latestPassword)
    {
        if ($latestPassword && $this->observerConfig->_isLatestPasswordExpired($latestPassword)) {
            if ($this->observerConfig->isPasswordChangeForced()) {
                $message = __('It\'s time to change your password.');
            } else {
                $myAccountUrl = $this->url->getUrl('adminhtml/system_account/');
                $message = __('It\'s time to <a href="%1">change your password</a>.', $myAccountUrl);
            }
            $this->messageManager->addNoticeMessage($message);
            $message = $this->messageManager->getMessages()->getLastAddedMessage();
            if ($message) {
                $message->setIdentifier('magento_user_password_expired')->setIsSticky(true);
                $this->authSession->setPciAdminUserIsPasswordExpired(true);
            }
        }
    }
}
