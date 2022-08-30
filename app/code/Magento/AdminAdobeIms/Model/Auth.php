<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdminAdobeIms\Model;

use Magento\Backend\Model\Auth as BackendAuth;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\Plugin\AuthenticationException as PluginAuthenticationException;

class Auth extends BackendAuth
{
    /**
     * @var string
     */
    private string $errorMessage = 'The account sign-in was incorrect or your account is disabled temporarily. '
        . 'Please wait and try again later.';

    /**
     * Perform login process without password
     *
     * @param string $username
     * @return void
     * @throws AuthenticationException
     * @SuppressWarnings(PHPCPD)
     */
    public function loginByUsername(string $username): void
    {
        if (empty($username)) {
            parent::throwException(
                __($this->errorMessage)
            );
        }

        try {
            $this->_initCredentialStorage();
            $this->getCredentialStorage()->loginByUsername($username);
            if ($this->getCredentialStorage()->getId()) {
                $this->getAuthStorage()->setUser($this->getCredentialStorage());
                $this->getAuthStorage()->processLogin();

                $this->_eventManager->dispatch(
                    'backend_auth_user_login_success',
                    ['user' => $this->getCredentialStorage()]
                );
            }

            if (!$this->getAuthStorage()->getUser()) {
                parent::throwException(
                    __($this->errorMessage)
                );
            }
        } catch (PluginAuthenticationException $e) {
            $this->_eventManager->dispatch(
                'backend_auth_user_login_failed',
                ['user_name' => $username, 'exception' => $e]
            );
            throw $e;
        } catch (LocalizedException $e) {
            $this->_eventManager->dispatch(
                'backend_auth_user_login_failed',
                ['user_name' => $username, 'exception' => $e]
            );
            parent::throwException(
                __(
                    $e->getMessage()? : $this->errorMessage
                )
            );
        }
    }
}
