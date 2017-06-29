<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Observer\Backend;

use Magento\Backend\Model\Auth\Session;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\User\Model\Backend\Config\ObserverConfig;
use Magento\User\Model\ResourceModel\User as ResourceUser;
use Magento\User\Model\User;
use Magento\Framework\Event\ObserverInterface;
use Magento\User\Model\UserFactory;

/**
 * User backend observer model for authentication
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AuthObserver implements ObserverInterface
{
    /**
     * Backend configuration interface
     *
     * @var ObserverConfig
     */
    protected $observerConfig;

    /**
     * Admin user resource model
     *
     * @var ResourceUser
     */
    protected $userResource;

    /**
     * Backend url interface
     *
     * @var UrlInterface
     */
    protected $url;

    /**
     * Backend authorization session
     *
     * @var Session
     */
    protected $authSession;

    /**
     * Factory class for user model
     *
     * @var UserFactory
     */
    protected $userFactory;

    /**
     * Encryption model
     *
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * Message manager interface
     *
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @param ObserverConfig $observerConfig
     * @param ResourceUser $userResource
     * @param UrlInterface $url
     * @param Session $authSession
     * @param UserFactory $userFactory
     * @param EncryptorInterface $encryptor
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        ObserverConfig $observerConfig,
        ResourceUser $userResource,
        UrlInterface $url,
        Session $authSession,
        UserFactory $userFactory,
        EncryptorInterface $encryptor,
        ManagerInterface $messageManager
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
    public function execute(EventObserver $observer)
    {
        $password = $observer->getEvent()->getPassword();
        /** @var User $user */
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

        if (!$this->encryptor->validateHashVersion($user->getPassword(), true)) {
            $user->setPassword($password)
                ->setData('force_new_password', true)
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
