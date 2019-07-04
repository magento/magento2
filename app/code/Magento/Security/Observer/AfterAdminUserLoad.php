<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Security\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Load UserExpiration on admin user. Only needed in the admin to load the expires_at when editing users.
 */
class AfterAdminUserLoad implements ObserverInterface
{
    /**
     * @var \Magento\Security\Model\UserExpirationFactory
     */
    private $userExpirationFactory;

    /**
     * @var \Magento\Security\Model\ResourceModel\UserExpiration
     */
    private $userExpirationResource;

    /**
     * AfterAdminUserLoad constructor.
     *
     * @param \Magento\Security\Model\UserExpirationFactory $userExpirationFactory
     * @param \Magento\Security\Model\ResourceModel\UserExpiration $userExpirationResource
     */
    public function __construct(
        \Magento\Security\Model\UserExpirationFactory $userExpirationFactory,
        \Magento\Security\Model\ResourceModel\UserExpiration $userExpirationResource
    ) {

        $this->userExpirationFactory = $userExpirationFactory;
        $this->userExpirationResource = $userExpirationResource;
    }

    /**
     * Set the user expiration date onto the user.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        /* @var $user \Magento\User\Model\User */
        $user = $observer->getEvent()->getObject();
        if ($user->getId()) {
            /** @var \Magento\Security\Model\UserExpiration $userExpiration */
            $userExpiration = $this->userExpirationFactory->create();
            $this->userExpirationResource->load($userExpiration, $user->getId());
            if ($userExpiration->getExpiresAt()) {
                $user->setExpiresAt($userExpiration->getExpiresAt());
            }
        }
    }
}
