<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Lock\Backend;

use Magento\Framework\Lock\LockManagerInterface;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Phrase;

/**
 * LockManager using the Zookeeper for locks
 */
class Zookeeper implements LockManagerInterface
{
    /**
     * Zookeeper provider
     *
     * @var \Zookeeper
     */
    private $zookeeper;

    /**
     * The base path to locks in Zookeeper
     *
     * @var string
     */
    private $path;

    /**
     * The name of sequence nodes
     *
     * @var string
     */
    private $lockName = 'lock-';

    /**
     * The host to connect to Zookeeper
     *
     * @var string
     */
    private $host;

    /**
     * How many seconds to wait before timing out on connections
     *
     * @var int
     */
    private $connectionTimeout = 2;

    /**
     * How many microseconds to wait before recheck connections or nodes
     *
     * @var int
     */
    private $sleepCycle = 100000;

    /**
     * The default permissions for Zookeeper nodes
     *
     * @var array
     */
    private $acl = [['perms'=>\Zookeeper::PERM_ALL, 'scheme' => 'world', 'id' => 'anyone']];

    /**
     * The mapping list of the lock name with the full lock path
     *
     * @var array
     */
    private $locks = [];

    /**
     * The default path to storage locks
     */
    const DEFAULT_PATH = '/magento/locks';

    /**
     * @param string $host The host to connect to Zookeeper
     * @param string $path The base path to locks in Zookeeper
     * @throws RuntimeException
     */
    public function __construct(string $host, string $path = self::DEFAULT_PATH)
    {
        if (!$path) {
            throw new RuntimeException(
                new Phrase('The path needs to be a non-empty string.')
            );
        }

        if (!$host) {
            throw new RuntimeException(
                new Phrase('The host needs to be a non-empty string.')
            );
        }

        $this->host = $host;
        $this->path = rtrim($path, '/') . '/';
    }

    /**
     * @inheritdoc
     *
     * You can see the lock algorithm by the link
     * @link https://zookeeper.apache.org/doc/r3.1.2/recipes.html#sc_recipes_Locks
     *
     * @throws RuntimeException
     */
    public function lock(string $name, int $timeout = -1): bool
    {
        $skipDeadline = $timeout < 0;
        $lockPath = $this->getFullPathToLock($name);
        $deadline = microtime(true) + $timeout;

        if (!$this->checkAndCreateParentNode($lockPath)) {
            throw new RuntimeException(new Phrase('Failed creating the path %1', [$lockPath]));
        }

        $lockKey = $this->getProvider()
            ->create($lockPath, '1', $this->acl, \Zookeeper::EPHEMERAL | \Zookeeper::SEQUENCE);

        if (!$lockKey) {
            throw new RuntimeException(new Phrase('Failed creating lock %1', [$lockPath]));
        }

        while ($this->isAnyLock($lockKey, $this->getIndex($lockKey))) {
            if (!$skipDeadline && $deadline <= microtime(true)) {
                $this->getProvider()->delete($lockKey);
                return false;
            }

            usleep($this->sleepCycle);
        }

        $this->locks[$name] = $lockKey;

        return true;
    }

    /**
     * @inheritdoc
     *
     * @throws RuntimeException
     */
    public function unlock(string $name): bool
    {
        if (!isset($this->locks[$name])) {
            return false;
        }

        return $this->getProvider()->delete($this->locks[$name]);
    }

    /**
     * @inheritdoc
     *
     * @throws RuntimeException
     */
    public function isLocked(string $name): bool
    {
        return $this->isAnyLock($this->getFullPathToLock($name));
    }

    /**
     * Gets full path to lock by its name
     *
     * @param string $name
     * @return string
     */
    private function getFullPathToLock(string $name): string
    {
        return $this->path . $name . '/' . $this->lockName;
    }

    /**
     * Initiolizes and returns Zookeeper provider
     *
     * @return \Zookeeper
     * @throws RuntimeException
     */
    private function getProvider(): \Zookeeper
    {
        if (!$this->zookeeper) {
            $this->zookeeper = new \Zookeeper($this->host);
        }

        $deadline = microtime(true) + $this->connectionTimeout;
        while ($this->zookeeper->getState() != \Zookeeper::CONNECTED_STATE) {
            if ($deadline <= microtime(true)) {
                throw new RuntimeException(new Phrase('Zookeeper connection timed out!'));
            }
            usleep($this->sleepCycle);
        }

        return $this->zookeeper;
    }

    /**
     * Checks and creates base path recursively
     *
     * @param string $path
     * @return bool
     * @throws RuntimeException
     */
    private function checkAndCreateParentNode(string $path): bool
    {
        $path = dirname($path);
        if ($this->getProvider()->exists($path)) {
            return true;
        }

        if (!$this->checkAndCreateParentNode($path)) {
            return false;
        }

        if ($this->getProvider()->create($path, '1', $this->acl)) {
            return true;
        }

        return $this->getProvider()->exists($path);
    }

    /**
     * Gets int increment of lock key
     *
     * @param string $key
     * @return int|null
     */
    private function getIndex(string $key)
    {
        if (!preg_match('/' . $this->lockName . '([0-9]+)$/', $key, $matches)) {
            return null;
        }

        return intval($matches[1]);
    }

    /**
     * Checks if there is any sequence node under parent of $fullKey.
     *
     * At first checks that the $fullKey node is present, if not - returns false.
     * If $indexKey is non-null and there is a smaller index than $indexKey then returns true,
     * otherwise returns false.
     *
     * @param string $fullKey The full path without any sequence info
     * @param int|null $indexKey The index to compare
     * @return bool
     * @throws RuntimeException
     */
    private function isAnyLock(string $fullKey, int $indexKey = null): bool
    {
        $parent = dirname($fullKey);

        if (!$this->getProvider()->exists($parent)) {
            return false;
        }

        $children = $this->getProvider()->getChildren($parent);

        if (null === $indexKey && !empty($children)) {
            return true;
        }

        foreach ($children as $childKey) {
            $childIndex = $this->getIndex($childKey);

            if (null === $childIndex) {
                continue;
            }

            if ($childIndex < $indexKey) {
                return true;
            }
        }

        return false;
    }
}
