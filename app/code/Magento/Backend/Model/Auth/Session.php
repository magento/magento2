<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Auth;

use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;

/**
 * Backend Auth session model
 *
 * @api
 * @method \Magento\User\Model\User|null getUser()
 * @method \Magento\Backend\Model\Auth\Session setUser(\Magento\User\Model\User $value)
 * @method \Magento\Framework\Acl|null getAcl()
 * @method \Magento\Backend\Model\Auth\Session setAcl(\Magento\Framework\Acl $value)
 * @method int getUpdatedAt()
 * @method \Magento\Backend\Model\Auth\Session setUpdatedAt(int $value)
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @todo implement solution that keeps is_first_visit flag in session during redirects
 * @api
 * @since 100.0.2
 */
class Session extends \Magento\Framework\Session\SessionManager implements \Magento\Backend\Model\Auth\StorageInterface
{
    /**
     * Admin session lifetime config path
     */
    const XML_PATH_SESSION_LIFETIME = 'admin/security/session_lifetime';

    /**
     * Whether it is the first page after successful login
     *
     * @var boolean
     */
    protected $_isFirstAfterLogin;

    /**
     * Access Control List builder
     *
     * @var \Magento\Framework\Acl\Builder
     */
    protected $_aclBuilder;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $_backendUrl;

    /**
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $_config;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\Session\Config\ConfigInterface $sessionConfig
     * @param \Magento\Framework\Session\SaveHandlerInterface $saveHandler
     * @param \Magento\Framework\Session\ValidatorInterface $validator
     * @param \Magento\Framework\Session\StorageInterface $storage
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\Acl\Builder $aclBuilder
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Magento\Backend\App\ConfigInterface $config
     * @throws \Magento\Framework\Exception\SessionException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig,
        \Magento\Framework\Session\SaveHandlerInterface $saveHandler,
        \Magento\Framework\Session\ValidatorInterface $validator,
        \Magento\Framework\Session\StorageInterface $storage,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\Acl\Builder $aclBuilder,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Magento\Backend\App\ConfigInterface $config
    ) {
        $this->_config = $config;
        $this->_aclBuilder = $aclBuilder;
        $this->_backendUrl = $backendUrl;
        parent::__construct(
            $request,
            $sidResolver,
            $sessionConfig,
            $saveHandler,
            $validator,
            $storage,
            $cookieManager,
            $cookieMetadataFactory,
            $appState
        );
    }

    /**
     * Refresh ACL resources stored in session
     *
     * @param  \Magento\User\Model\User $user
     * @return \Magento\Backend\Model\Auth\Session
     */
    public function refreshAcl($user = null)
    {
        if ($user === null) {
            $user = $this->getUser();
        }
        if (!$user) {
            return $this;
        }
        if (!$this->getAcl() || $user->getReloadAclFlag()) {
            $this->setAcl($this->_aclBuilder->getAcl());
        }
        if ($user->getReloadAclFlag()) {
            $user->unsetData('password');
            $user->setReloadAclFlag('0')->save();
        }
        return $this;
    }

    /**
     * Check current user permission on resource and privilege
     *
     * @param   string $resource
     * @param   string $privilege
     * @return  boolean
     */
    public function isAllowed($resource, $privilege = null)
    {
        $user = $this->getUser();
        $acl = $this->getAcl();

        if ($user && $acl) {
            try {
                return $acl->isAllowed($user->getAclRole(), $resource, $privilege);
            } catch (\Exception $e) {
                try {
                    if (!$acl->has($resource)) {
                        return $acl->isAllowed($user->getAclRole(), null, $privilege);
                    }
                } catch (\Exception $e) {
                }
            }
        }
        return false;
    }

    /**
     * Check if user is logged in
     *
     * @return boolean
     */
    public function isLoggedIn()
    {
        return $this->getUser() && $this->getUser()->getId();
    }

    /**
     * Set session UpdatedAt to current time
     *
     * @return void
     */
    public function prolong()
    {
        $lifetime = $this->_config->getValue(self::XML_PATH_SESSION_LIFETIME);
        $cookieValue = $this->cookieManager->getCookie($this->getName());

        if ($cookieValue) {
            $this->setUpdatedAt(time());
            $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
                ->setDuration($lifetime)
                ->setPath($this->sessionConfig->getCookiePath())
                ->setDomain($this->sessionConfig->getCookieDomain())
                ->setSecure($this->sessionConfig->getCookieSecure())
                ->setHttpOnly($this->sessionConfig->getCookieHttpOnly());
            $this->cookieManager->setPublicCookie($this->getName(), $cookieValue, $cookieMetadata);
        }
    }

    /**
     * Check if it is the first page after successful login
     *
     * @return bool
     */
    public function isFirstPageAfterLogin()
    {
        if ($this->_isFirstAfterLogin === null) {
            $this->_isFirstAfterLogin = $this->getData('is_first_visit', true);
        }
        return $this->_isFirstAfterLogin;
    }

    /**
     * Setter whether the current/next page should be treated as first page after login
     *
     * @param bool $value
     * @return \Magento\Backend\Model\Auth\Session
     */
    public function setIsFirstPageAfterLogin($value)
    {
        $this->_isFirstAfterLogin = (bool)$value;
        return $this->setIsFirstVisit($this->_isFirstAfterLogin);
    }

    /**
     * Process of configuring of current auth storage when login was performed
     *
     * @return \Magento\Backend\Model\Auth\Session
     */
    public function processLogin()
    {
        if ($this->getUser()) {
            $this->regenerateId();

            if ($this->_backendUrl->useSecretKey()) {
                $this->_backendUrl->renewSecretUrls();
            }

            $this->setIsFirstPageAfterLogin(true);
            $this->setAcl($this->_aclBuilder->getAcl());
            $this->setUpdatedAt(time());
        }
        return $this;
    }

    /**
     * Process of configuring of current auth storage when logout was performed
     *
     * @return \Magento\Backend\Model\Auth\Session
     */
    public function processLogout()
    {
        $this->destroy();
        return $this;
    }

    /**
     * Skip path validation in backend area
     *
     * @param string $path
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @codeCoverageIgnore
     */
    public function isValidForPath($path)
    {
        return true;
    }
}
