<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Backend
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Backend\Model\Auth;

/**
 * Backend Auth session model
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Session
    extends \Magento\Core\Model\Session\AbstractSession
    implements \Magento\Backend\Model\Auth\StorageInterface
{
    const XML_PATH_SESSION_LIFETIME = 'admin/security/session_lifetime';

    /**
     * Whether it is the first page after successfull login
     *
     * @var boolean
     */
    protected $_isFirstAfterLogin;

    /**
     * Access Control List builder
     *
     * @var \Magento\Acl\Builder
     */
    protected $_aclBuilder;

    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $_backendUrl;

    /**
     * @var \Magento\Backend\App\ConfigInterface
     */
    protected $_config;

    /**
     * @param \Magento\Core\Model\Session\Context $context
     * @param \Magento\Session\SidResolverInterface $sidResolver
     * @param \Magento\Session\Config\ConfigInterface $sessionConfig
     * @param \Magento\Acl\Builder $aclBuilder
     * @param \Magento\Backend\Model\Url $backendUrl
     * @param \Magento\Backend\App\ConfigInterface $config
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Model\Session\Context $context,
        \Magento\Session\SidResolverInterface $sidResolver,
        \Magento\Session\Config\ConfigInterface $sessionConfig,
        \Magento\Acl\Builder $aclBuilder,
        \Magento\Backend\Model\Url $backendUrl,
        \Magento\Backend\App\ConfigInterface $config,
        array $data = array()
    ) {
        $this->_config = $config;
        $this->_aclBuilder = $aclBuilder;
        $this->_backendUrl = $backendUrl;
        parent::__construct($context, $sidResolver, $sessionConfig, $data);
        $this->start('admin');
    }

    /**
     * Pull out information from session whether there is currently the first page after log in
     *
     * The idea is to set this value on login(), then redirect happens,
     * after that on next request the value is grabbed once the session is initialized
     * Since the session is used as a singleton, the value will be in $_isFirstPageAfterLogin until the end of request,
     * unless it is reset intentionally from somewhere
     *
     * @param string $namespace
     * @param string $sessionName
     * @return \Magento\Backend\Model\Auth\Session
     * @see self::login()
     */
    public function start($namespace = 'default', $sessionName = null)
    {
        parent::start($namespace, $sessionName);
        // @todo implement solution that keeps is_first_visit flag in session during redirects
        return $this;
    }

    /**
     * Refresh ACL resources stored in session
     *
     * @param  \Magento\User\Model\User $user
     * @return \Magento\Backend\Model\Auth\Session
     */
    public function refreshAcl($user = null)
    {
        if (is_null($user)) {
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
        $lifetime = $this->_config->getValue(self::XML_PATH_SESSION_LIFETIME);
        $currentTime = time();

        /* Validate admin session lifetime that should be more than 60 seconds */
        if ($lifetime >= 60 && ($this->getUpdatedAt() < $currentTime - $lifetime)) {
            return false;
        }

        if ($this->getUser() && $this->getUser()->getId()) {
            $this->setUpdatedAt($currentTime);
            return true;
        }
        return false;
    }

    /**
     * Check if it is the first page after successfull login
     *
     * @return bool
     */
    public function isFirstPageAfterLogin()
    {
        if (is_null($this->_isFirstAfterLogin)) {
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
     */
    public function isValidForPath($path)
    {
        return true;
    }
}
