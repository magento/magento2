<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Observer\Backend;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * User backend observer model for passwords
 * @since 2.0.0
 */
class TrackAdminNewPasswordObserver implements ObserverInterface
{
    /**
     * Backend configuration interface
     *
     * @var \Magento\User\Model\Backend\Config\ObserverConfig
     * @since 2.0.0
     */
    protected $observerConfig;

    /**
     * Admin user resource model
     *
     * @var \Magento\User\Model\ResourceModel\User
     * @since 2.0.0
     */
    protected $userResource;

    /**
     * Backend authorization session
     *
     * @var \Magento\Backend\Model\Auth\Session
     * @since 2.0.0
     */
    protected $authSession;

    /**
     * Message manager interface
     *
     * @var \Magento\Framework\Message\ManagerInterface
     * @since 2.0.0
     */
    protected $messageManager;

    /**
     * @param \Magento\User\Model\Backend\Config\ObserverConfig $observerConfig
     * @param \Magento\User\Model\ResourceModel\User $userResource
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @since 2.0.0
     */
    public function __construct(
        \Magento\User\Model\Backend\Config\ObserverConfig $observerConfig,
        \Magento\User\Model\ResourceModel\User $userResource,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Message\ManagerInterface $messageManager
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
     * @since 2.0.0
     */
    public function execute(EventObserver $observer)
    {
        /* @var $user \Magento\User\Model\User */
        $user = $observer->getEvent()->getObject();
        if ($user->getId()) {
            $passwordHash = $user->getPassword();
            if ($passwordHash && !$user->getForceNewPassword()) {
                $this->userResource->trackPassword($user, $passwordHash);
                $this->messageManager->getMessages()->deleteMessageByIdentifier('magento_user_password_expired');
                $this->authSession->unsPciAdminUserIsPasswordExpired();
            }
        }
    }
}
