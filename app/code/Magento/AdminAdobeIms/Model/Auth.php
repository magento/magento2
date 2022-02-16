<?php

namespace Magento\AdminAdobeIms\Model;

use Magento\Backend\Model\Auth as BackendAuth;
use Magento\Framework\Exception\Plugin\AuthenticationException as PluginAuthenticationException;

class Auth extends BackendAuth
{
    /**
     * Perform login process without password
     *
     * @param string $username
     * @return void
     * @throws \Magento\Framework\Exception\AuthenticationException
     */
    public function loginByUsername(string $username): void
    {
        if (empty($username)) {
            self::throwException(
                __(
                    'The account sign-in was incorrect or your account is disabled temporarily. '
                    . 'Please wait and try again later.'
                )
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
                self::throwException(
                    __(
                        'The account sign-in was incorrect or your account is disabled temporarily. '
                        . 'Please wait and try again later.'
                    )
                );
            }
        } catch (PluginAuthenticationException $e) {
            $this->_eventManager->dispatch(
                'backend_auth_user_login_failed',
                ['user_name' => $username, 'exception' => $e]
            );
            throw $e;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_eventManager->dispatch(
                'backend_auth_user_login_failed',
                ['user_name' => $username, 'exception' => $e]
            );
            self::throwException(
                __(
                    $e->getMessage()? : 'The account sign-in was incorrect or your account is disabled temporarily. '
                        . 'Please wait and try again later.'
                )
            );
        }
    }
}