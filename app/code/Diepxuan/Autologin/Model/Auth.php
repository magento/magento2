<?php

declare(strict_types=1);

/*
 * @copyright  © 2019 Dxvn, Inc.
 *
 * @author     Tran Ngoc Duc <ductn@diepxuan.com>
 * @author     Tran Ngoc Duc <caothu91@gmail.com>
 *
 * @lastupdate 2025-04-01 13:08:44
 */

namespace Diepxuan\Autologin\Model;

use Magento\Backend\Helper\Data;
use Magento\Backend\Model\Auth\StorageInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Collection\ModelFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\AuthenticationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\Plugin\AuthenticationException as PluginAuthenticationException;
use Magento\Framework\Phrase;
use Magento\User\Model\User;
use Psr\Log\LoggerInterface;

/**
 * Backend Auth model.
 *
 * @see   \Magento\Backend\Model\Auth
 */
class Auth
{
    const ENABLE   = 'diepxuan_autologin/autologin/enable';
    const USERNAME = 'diepxuan_autologin/autologin/username';
    const ALLOWS   = 'diepxuan_autologin/autologin/allows';

    /**
     * @var Context
     */
    protected $_context;

    /**
     * @var LoggerInterface
     */
    protected $_logger;

    /**
     * @var Http
     */
    protected $_request;

    /**
     * Core event manager proxy.
     *
     * @var ManagerInterface
     */
    protected $_eventManager;

    /**
     * Backend data.
     *
     * @var Data
     */
    protected $_backendData;

    /**
     * @var \Magento\Backend\Model\Auth
     */
    protected $_auth;

    /**
     * @var StorageInterface
     */
    protected $_authStorage;

    /**
     * @var \Magento\Backend\Model\Auth\Credential\StorageInterface
     */
    protected $_credentialStorage;

    /**
     * @var ScopeConfigInterface
     */
    protected $_coreConfig;

    /**
     * @var ModelFactory
     */
    protected $_modelFactory;

    public function __construct(
        Context $context
    ) {
        $this->_context      = $context;
        $this->_logger       = $context->getLogger();
        $this->_request      = $context->getRequest();
        $this->_eventManager = $context->getEventManager();
        $this->_backendData  = $context->getBackendData();
        $this->_auth         = $context->getAuth();
        $this->_authStorage  = $context->getAuthStorage();
        $this->_coreConfig   = $context->getCoreConfig();
        $this->_modelFactory = $context->getModelFactory();
    }

    /**
     * Check if current user is logged in.
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        try {
            $this->_autoAuthentication();
        } catch (LocalizedException $e) {
            return $this->getAuthStorage()->isLoggedIn();
        }

        return $this->getAuthStorage()->isLoggedIn();
    }

    /**
     * Perform login process.
     *
     * @throws AuthenticationException
     */
    public function autoAuthentication(): void
    {
        try {
            $this->_initCredentialStorage();
            $this->getCredentialStorage()->loadByUsername($this->getAdminUserName());
            if ($this->getCredentialStorage()->getId()) {
                $this->getAuth()->login($this->getCredentialStorage()->getUserName(), $this->getCredentialStorage()->getPassword());

                $this->_eventManager->dispatch(
                    'backend_auth_user_login_success',
                    ['user' => $this->getCredentialStorage()]
                );
            }
            if (!$this->getAuthStorage()->getUser()) {
                self::throwException(__('You did not sign in correctly or your account is temporarily disabled.'));
            }
        } catch (PluginAuthenticationException $e) {
            $this->_eventManager->dispatch(
                'backend_auth_user_login_failed',
                ['user_name' => $this->getAdminUserName(), 'exception' => $e]
            );

            throw $e;
        } catch (LocalizedException $e) {
            $this->_eventManager->dispatch(
                'backend_auth_user_login_failed',
                ['user_name' => $this->getAdminUserName(), 'exception' => $e]
            );
            self::throwException(
                __($e->getMessage() ?: 'You did not sign in correctly or your account is temporarily disabled.')
            );
        }
    }

    /**
     * Check if the current user account is active.
     *
     * @param bool $result
     *
     * @return bool
     *
     * @throws AuthenticationException
     */
    public function verifyIdentity(User $user, $result = false)
    {
        if (!$this->isEnable()) {
            return $result;
        }

        if ($user->getUserName() === $this->getAdminUserName()) {
            $result = true;
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function isEnable()
    {
        return $this->_isEnable();
    }

    /**
     * @return string
     *
     * @throws \Exception
     */
    public function getAdminUserName()
    {
        $username = $this->_coreConfig->getValue(self::USERNAME);

        if (empty($username)) {
            self::throwException(__('Autologin/Authentication | Your admin username was not found!'));
        }

        return $username;
    }

    /**
     * Perform logout process.
     */
    public function logout(): void
    {
        $this->getAuthStorage()->processLogout();
    }

    /**
     * Throws specific Backend Authentication \Exception.
     *
     * @throws AuthenticationException
     *
     * @static
     */
    public static function throwException(?Phrase $msg = null): void
    {
        if (null === $msg) {
            $msg = __('Authentication error occurred.');
        }

        throw new AuthenticationException($msg);
    }

    /**
     * @return \Magento\Backend\Model\Auth
     *
     * @codeCoverageIgnore
     */
    public function getAuth()
    {
        return $this->_auth;
    }

    /**
     * Return auth storage.
     * If auth storage was not defined outside - returns default object of auth storage.
     *
     * @return StorageInterface
     *
     * @codeCoverageIgnore
     */
    public function getAuthStorage()
    {
        return $this->_authStorage;
    }

    /**
     * Return current (successfully authenticated) user,
     * an instance of \Magento\Backend\Model\Auth\Credential\StorageInterface.
     *
     * @return \Magento\Backend\Model\Auth\Credential\StorageInterface
     */
    public function getUser()
    {
        return $this->getAuthStorage()->getUser();
    }

    /**
     * Return credential storage object.
     *
     * @return null|\Magento\Backend\Model\Auth\Credential\StorageInterface
     *
     * @codeCoverageIgnore
     */
    public function getCredentialStorage()
    {
        return $this->_credentialStorage;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * Initialize credential storage from configuration.
     */
    protected function _initCredentialStorage(): void
    {
        $this->_credentialStorage = $this->_modelFactory->create(
            User::class
        );
    }

    protected function _autoAuthentication()
    {
        if (!$this->isEnable()) {
            return;
        }

        return $this->autoAuthentication();
    }

    /**
     * @return bool
     */
    protected function _isEnable()
    {
        if ($this->getAuthStorage()->isLoggedIn()) {
            return false;
        }

        $enable = $this->_coreConfig->getValue(self::ENABLE);
        if (!$enable) {
            return false;
        }

        return true;
    }
}
