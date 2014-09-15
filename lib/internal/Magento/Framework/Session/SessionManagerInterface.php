<?php
/**
 * Magento session manager interface
 *
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Session;

/**
 * Session Manager Interface
 */
interface SessionManagerInterface
{
    /**
     * Session key for list of hosts
     */
    const HOST_KEY = '_session_hosts';

    /**
     * Start session
     *
     * @return SessionManagerInterface
     */
    public function start();

    /**
     * Session write close
     *
     * @return void
     */
    public function writeClose();

    /**
     * Does a session exist
     *
     * @return bool
     */
    public function isSessionExists();

    /**
     * Retrieve session Id
     *
     * @return string
     */
    public function getSessionId();

    /**
     * Retrieve session name
     *
     * @return string
     */
    public function getName();

    /**
     * Set session name
     *
     * @param string $name
     * @return SessionManagerInterface
     */
    public function setName($name);

    /**
     * Destroy/end a session
     *
     * @param  array $options
     * @return void
     */
    public function destroy(array $options = null);

    /**
     * Unset session data
     *
     * @return $this
     */
    public function clearStorage();

    /**
     * Retrieve Cookie domain
     *
     * @return string
     */
    public function getCookieDomain();

    /**
     * Retrieve cookie path
     *
     * @return string
     */
    public function getCookiePath();

    /**
     * Retrieve cookie lifetime
     *
     * @return int
     */
    public function getCookieLifetime();

    /**
     * Specify session identifier
     *
     * @param string|null $sessionId
     * @return SessionManagerInterface
     */
    public function setSessionId($sessionId);

    /**
     * Renew session id and update session cookie
     *
     * @return SessionManagerInterface
     */
    public function regenerateId();

    /**
     * Expire the session cookie
     *
     * Sends a session cookie with no value, and with an expiry in the past.
     *
     * @return void
     */
    public function expireSessionCookie();

    /**
     * If session cookie is not applicable due to host or path mismatch - add session id to query
     *
     * @param string $urlHost
     * @return string
     */
    public function getSessionIdForHost($urlHost);

    /**
     * Check if session is valid for given hostname
     *
     * @param string $host
     * @return bool
     */
    public function isValidForHost($host);

    /**
     * Check if session is valid for given path
     *
     * @param string $path
     * @return bool
     */
    public function isValidForPath($path);
}
