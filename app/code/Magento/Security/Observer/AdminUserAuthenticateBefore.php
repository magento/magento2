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
use Magento\User\Model\UserFactory;

/**
 * Check for expired users.
 */
class AdminUserAuthenticateBefore implements ObserverInterface
{
    /**
     * @var UserExpirationManager
     */
    private $userExpirationManager;

    /**
     * @var UserFactory
     */
    private $userFactory;

    /**
     * AdminUserAuthenticateBefore constructor.
     *
     * @param UserExpirationManager $userExpirationManager
     * @param UserFactory $userFactory
     */
    public function __construct(
        UserExpirationManager $userExpirationManager,
        UserFactory $userFactory
    ) {
        $this->userExpirationManager = $userExpirationManager;
        $this->userFactory = $userFactory;
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
        $user = $this->userFactory->create();
        /** @var \Magento\User\Model\User $user */
        $user->loadByUsername($username);

        if ($user->getId() && $this->userExpirationManager->isUserExpired($user->getId())) {
            $this->userExpirationManager->deactivateExpiredUsersById([$user->getId()]);
            throw new AuthenticationException(
                __(
                    'The account sign-in was incorrect or your account is disabled temporarily. '
                    . 'Please wait and try again later.'
                )
            );
        }
    }
}
