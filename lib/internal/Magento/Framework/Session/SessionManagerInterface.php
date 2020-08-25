<?php
/**
 * Magento session manager interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session;

/**
 * Session Manager Interface
 *
 * @api
 * @since 100.0.2
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
