<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\Plugin\AuthenticationException;
use Magento\Security\Model\UserExpirationManager;

/**
 * Check for expired users.
 *
 * @package Magento\Security\Observer
 */
class AdminUserAuthenticateBefore implements ObserverInterface
{
    /**
     * @var UserExpirationManager
     */
    private $userExpirationManager;

    /**
     * @var \Magento\User\Model\User
     */
    private $user;

    /**
     * AdminUserAuthenticateBefore constructor.
     *
     * @param UserExpirationManager $userExpirationManager
     * @param \Magento\User\Model\User $user
     */
    public function __construct(
        \Magento\Security\Model\UserExpirationManager $userExpirationManager,
        \Magento\User\Model\User $user
    ) {
        $this->userExpirationManager = $userExpirationManager;
        $this->user = $user;
    }

    /**
     * Check for expired user when logging in.
     *
     * @param Observer $observer
     * @return void
     * @throws AuthenticationException
     */
    public function execute(Observer $observer)
    {
        $username = $observer->getEvent()->getUsername();
        /** @var \Magento\User\Model\User $user */
        $user = $this->user->loadByUsername($username);

        if ($this->userExpirationManager->userIsExpired($user)) {
            $this->userExpirationManager->deactivateExpiredUsers([$user->getId()]);
            throw new AuthenticationException(
                __(
                    'The account sign-in was incorrect or your account is disabled temporarily. '
                    . 'Please wait and try again later.'
                )
            );
        }
    }
}
