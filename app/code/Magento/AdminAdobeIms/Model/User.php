<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model;

use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\User\Model\User as AdminUser;
use Magento\AdminAdobeIms\Model\ResourceModel\User as AdminResourceUser;

class User extends AdminUser
{
    /**
     * Initialize user model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(AdminResourceUser::class);
    }

    /**
     * Load user by email
     *
     * @param string $email
     * @return array
     */
    public function loadByEmail(string $email): array
    {
        return $this->getResource()->loadByEmail($email);
    }

    /**
     * Login user
     *
     * @param string $username
     * @return User
     * @throws LocalizedException
     */
    public function loginByUsername($username): User
    {
        if ($this->authenticateByUsername($username)) {
            $this->getResource()->recordLogin($this);
        }
        return $this;
    }

    /**
     * Authenticate username and save loaded record
     *
     * @param string $username
     * @return bool
     * @throws LocalizedException
     */
    private function authenticateByUsername(string $username): bool
    {
        $config = $this->_config->isSetFlag('admin/security/use_case_sensitive_login');
        $result = false;

        try {
            $this->_eventManager->dispatch(
                'admin_user_authenticate_before',
                ['username' => $username, 'user' => $this]
            );
            $this->loadByUsername($username);
            $sensitive = !$config || $username === $this->getUserName();
            if ($sensitive && $this->getId()) {
                $result = $this->verifyIdentityWithoutPassword();
            }

            /**
             * Dispatch admin_user_authenticate_after but with an empty password
             */
            $this->_eventManager->dispatch(
                'admin_adobe_ims_user_authenticate_after',
                ['username' => $username, 'user' => $this, 'result' => $result]
            );

        } catch (LocalizedException $e) {
            $this->unsetData();
            throw $e;
        }

        if (!$result) {
            $this->unsetData();
        }
        return $result;
    }

    /**
     * Check if the current user account is active.
     *
     * @return bool
     * @throws AuthenticationException
     */
    private function verifyIdentityWithoutPassword(): bool
    {
        if ((bool)$this->getIsActive() === false) {
            throw new AuthenticationException(
                __(
                    'The account sign-in was incorrect or your account is disabled temporarily. '
                    . 'Please wait and try again later.'
                )
            );
        }
        if (!$this->hasAssigned2Role($this->getId())) {
            throw new AuthenticationException(__('More permissions are needed to access this.'));
        }

        return true;
    }
}
