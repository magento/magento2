<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Observer\Backend;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\User\Model\User;
use Magento\User\Model\Backend\Config\ObserverConfig;
use Magento\User\Model\ResourceModel\User as UserResource;
use Magento\Backend\Model\Auth\Session as AuthSession;

/**
 * User backend observer model for passwords
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class TrackAdminNewPasswordObserver implements ObserverInterface
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
     * @var UserResource
     */
    protected $userResource;

    /**
     * Backend authorization session
     *
     * @var AuthSession
     */
    protected $authSession;

    /**
     * Message manager interface
     *
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @param ObserverConfig $observerConfig
     * @param UserResource $userResource
     * @param AuthSession $authSession
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        ObserverConfig $observerConfig,
        UserResource $userResource,
        AuthSession $authSession,
        ManagerInterface $messageManager
    ) {
        $this->observerConfig = $observerConfig;
        $this->userResource = $userResource;
        $this->authSession = $authSession;
        $this->messageManager = $messageManager;
    }

    /**
     * Save current admin password to prevent its usage when changed in the future.
     *
     * @param EventObserver $observer
     * @return void
     */
    public function execute(EventObserver $observer)
    {
        /* @var $user User */
        $user = $observer->getEvent()->getObject();
        if ($user->getId()) {
            $passwordHash = $user->getPassword();
            if ($passwordHash && $user->dataHasChangedFor('password')) {
                $this->userResource->trackPassword($user, $passwordHash);
                $this->messageManager->getMessages()->deleteMessageByIdentifier(User::MESSAGE_ID_PASSWORD_EXPIRED);
                $this->authSession->unsPciAdminUserIsPasswordExpired();
            }
        }
    }
}
