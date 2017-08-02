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
 * @since 2.0.0
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
     * @since 2.0.0
     */
    public function start();

    /**
     * Session write close
     *
     * @return void
     * @since 2.0.0
     */
    public function writeClose();

    /**
     * Does a session exist
     *
     * @return bool
     * @since 2.0.0
     */
    public function isSessionExists();

    /**
     * Retrieve session Id
     *
     * @return string
     * @since 2.0.0
     */
    public function getSessionId();

    /**
     * Retrieve session name
     *
     * @return string
     * @since 2.0.0
     */
    public function getName();

    /**
     * Set session name
     *
     * @param string $name
     * @return SessionManagerInterface
     * @since 2.0.0
     */
    public function setName($name);

    /**
     * Destroy/end a session
     *
     * @param  array $options
     * @return void
     * @since 2.0.0
     */
    public function destroy(array $options = null);

    /**
     * Unset session data
     *
     * @return $this
     * @since 2.0.0
     */
    public function clearStorage();

    /**
     * Retrieve Cookie domain
     *
     * @return string
     * @since 2.0.0
     */
    public function getCookieDomain();

    /**
     * Retrieve cookie path
     *
     * @return string
     * @since 2.0.0
     */
    public function getCookiePath();

    /**
     * Retrieve cookie lifetime
     *
     * @return int
     * @since 2.0.0
     */
    public function getCookieLifetime();

    /**
     * Specify session identifier
     *
     * @param string|null $sessionId
     * @return SessionManagerInterface
     * @since 2.0.0
     */
    public function setSessionId($sessionId);

    /**
     * Renew session id and update session cookie
     *
     * @return SessionManagerInterface
     * @since 2.0.0
     */
    public function regenerateId();

    /**
     * Expire the session cookie
     *
     * Sends a session cookie with no value, and with an expiry in the past.
     *
     * @return void
     * @since 2.0.0
     */
    public function expireSessionCookie();

    /**
     * If session cookie is not applicable due to host or path mismatch - add session id to query
     *
     * @param string $urlHost
     * @return string
     * @since 2.0.0
     */
    public function getSessionIdForHost($urlHost);

    /**
     * Check if session is valid for given hostname
     *
     * @param string $host
     * @return bool
     * @since 2.0.0
     */
    public function isValidForHost($host);

    /**
     * Check if session is valid for given path
     *
     * @param string $path
     * @return bool
     * @since 2.0.0
     */
    public function isValidForPath($path);
}
