<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Lock\Backend;

use Magento\Framework\Cache\FrontendInterface;

/**
 * Implementation of the lock manager on the basis of the caching system.
 */
class Cache implements \Magento\Framework\Lock\LockManagerInterface
{
    /**
     * Prefix for marking that key is locked or not.
     */
    const LOCK_PREFIX = 'LOCKED_RECORD_INFO_';

    /**
     * @var FrontendInterface
     */
    private $cache;

    /**
     * Sign for locks, helps to avoid removing a lock that was created by another client
     *
     * @string
     */
    private $lockSign;

    /**
     * @param FrontendInterface $cache
     */
    public function __construct(FrontendInterface $cache)
    {
        $this->cache = $cache;
        $this->lockSign = $this->generateLockSign();
    }

    /**
     * @inheritdoc
     */
    public function lock(string $name, int $timeout = -1): bool
    {
        if (empty($this->lockSign)) {
            $this->lockSign = $this->generateLockSign();
        }

        $data = $this->cache->load($this->getIdentifier($name));

        if (false !== $data) {
             return false;
        }

        $timeout = $timeout <= 0 ? null : $timeout;
        $this->cache->save($this->lockSign, $this->getIdentifier($name), [], $timeout);

        $data = $this->cache->load($this->getIdentifier($name));

        if ($data === $this->lockSign) {
            return true;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function unlock(string $name): bool
    {
        if (empty($this->lockSign)) {
            return false;
        }

        $data = $this->cache->load($this->getIdentifier($name));

        if (false === $data) {
            return false;
        }

        $removeResult = false;
        if ($data === $this->lockSign) {
            $removeResult = (bool)$this->cache->remove($this->getIdentifier($name));
        }

        return $removeResult;
    }

    /**
     * @inheritdoc
     */
    public function isLocked(string $name): bool
    {
        return (bool)$this->cache->test($this->getIdentifier($name));
    }

    /**
     * Get cache locked identifier based on cache identifier.
     *
     * @param string $cacheIdentifier
     * @return string
     */
    private function getIdentifier(string $cacheIdentifier): string
    {
        return self::LOCK_PREFIX . $cacheIdentifier;
    }

    /**
     * Function that generates lock sign that helps to avoid removing a lock that was created by another client.
     *
     * @return string
     */
    private function generateLockSign()
    {
        $sign = implode(
            '-',
            [
                \getmypid(), \crc32(\gethostname())
            ]
        );

        try {
            $sign .= '-' . \bin2hex(\random_bytes(4));
        } catch (\Exception $e) {
            $sign .= '-' . \uniqid('-uniqid-');
        }

        return $sign;
    }
}
