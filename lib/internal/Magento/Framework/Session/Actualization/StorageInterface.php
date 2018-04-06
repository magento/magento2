<?php
/**
 * Session Actualization Storage interface
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session\Actualization;

/**
 * Session Actualization Storage interface
 */
interface StorageInterface extends \Magento\Framework\Session\StorageInterface
{
    const STORAGE_NAMESPACE = 'actualization';

    const SESSION_OLD_TIMESTAMP = 'destroyed';

    const NEW_SESSION_ID = 'new_session_id';

    const OLD_SESSION_ID = 'old_session_id';

    /**
     * Get actual session id.
     *
     * @return string
     */
    public function getNewSessionId();

    /**
     * Set actual session id.
     *
     * @param string $sessionId
     * @return $this
     */
    public function setNewSessionId($sessionId);

    /**
     * Check if session storage contains info about actual session.
     *
     * @return bool
     */
    public function hasNewSessionId();

    /**
     * Get old session id.
     *
     * @return string
     */
    public function getOldSessionId();

    /**
     * Set old session id.
     *
     * @param string $sessionId
     * @return $this
     */
    public function setOldSessionId($sessionId);

    /**
     * Check if actual session contains id of old session.
     *
     * @return bool
     */
    public function hasOldSessionId();

    /**
     * Remove old session id.
     *
     * @return $this
     */
    public function unsOldSessionId();

    /**
     * Get timestamp when session become deprecated.
     *
     * @return int
     */
    public function getSessionOldTimestamp();

    /**
     * Set session as deprecated.
     *
     * @return $this
     */
    public function setSessionOldTimestamp();
}
