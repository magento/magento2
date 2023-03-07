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
     * @param ObserverConfig $observerConfig
     * @param UserResource $userResource
     * @param AuthSession $authSession
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        protected readonly ObserverConfig $observerConfig,
        protected readonly UserResource $userResource,
        protected readonly AuthSession $authSession,
        protected readonly ManagerInterface $messageManager
    ) {
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
