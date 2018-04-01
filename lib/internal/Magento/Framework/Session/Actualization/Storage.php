<?php
/**
 * Session Actualization Storage
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session\Actualization;

/**
 * Actualization Storage
 * Store required information for session actualization in conditions of unstable connection.
 */
class Storage extends \Magento\Framework\Session\Storage implements
    \Magento\Framework\Session\Actualization\StorageInterface
{
    /**
     * Storage constructor.
     *
     * @param string $namespace
     * @param array $data
     */
    public function __construct($namespace = self::STORAGE_NAMESPACE, array $data = [])
    {
        parent::__construct($namespace, $data);
    }

    /**
     * Get actual session id.
     *
     * @return string
     */
    public function getNewSessionId()
    {
        return $this->getData(self::NEW_SESSION_ID);
    }

    /**
     * Set actual session id.
     *
     * @param string $sessionId
     * @return $this
     */
    public function setNewSessionId($sessionId)
    {
        return $this->setData(self::NEW_SESSION_ID, $sessionId);
    }

    /**
     * Check if session storage contains info about actual session.
     *
     * @return bool
     */
    public function hasNewSessionId()
    {
        return $this->hasData(self::NEW_SESSION_ID);
    }

    /**
     * Get old session id.
     *
     * @return string
     */
    public function getOldSessionId()
    {
        return $this->getData(self::OLD_SESSION_ID);
    }

    /**
     * Set old session id.
     *
     * @param string $sessionId
     * @return $this
     */
    public function setOldSessionId($sessionId)
    {
        return $this->setData(self::OLD_SESSION_ID, $sessionId);
    }

    /**
     * Check if actual session contains id of old session.
     *
     * @return bool
     */
    public function hasOldSessionId()
    {
        return $this->hasData(self::OLD_SESSION_ID);
    }

    /**
     * Remove old session id.
     *
     * @return $this
     */
    public function unsOldSessionId()
    {
        return $this->unsetData(self::OLD_SESSION_ID);
    }

    /**
     * Get timestamp when session become deprecated.
     *
     * @return int
     */
    public function getSessionOldTimestamp()
    {
        return $this->getData(self::SESSION_OLD_TIMESTAMP);
    }

    /**
     * Set session as deprecated.
     *
     * @return $this
     */
    public function setSessionOldTimestamp()
    {
        return $this->setData(self::SESSION_OLD_TIMESTAMP, time());
    }
}
