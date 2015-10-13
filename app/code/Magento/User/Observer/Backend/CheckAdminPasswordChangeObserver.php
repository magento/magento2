<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\User\Observer\Backend;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * User backend observer model for passwords
 */
class CheckAdminPasswordChangeObserver implements ObserverInterface
{
    /**
     * Admin user resource model
     *
     * @var \Magento\User\Model\ResourceModel\User
     */
    protected $userResource;

    /**
     * Encryption model
     *
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $encryptor;

    /**
     * @param \Magento\User\Model\ResourceModel\User $userResource
     * @param \Magento\Framework\Encryption\EncryptorInterface $encryptor
     */
    public function __construct(
        \Magento\User\Model\ResourceModel\User $userResource,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        $this->userResource = $userResource;
        $this->encryptor = $encryptor;
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
    public function execute(EventObserver $observer)
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
            $passwordHash = $this->encryptor->getHash($password, false);
            foreach ($this->userResource->getOldPasswords($user) as $oldPasswordHash) {
                if ($passwordHash === $oldPasswordHash) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Sorry, but this password has already been used. Please create another.')
                    );
                }
            }
        }
    }
}
