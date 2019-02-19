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
     * @var float
     */
    private $sleepCycle = 100000;

    /**
     * The default permissions for Zookeeper nodes
     *
     * @var array
     */
    private $acl = [['perms'=>\Zookeeper::PERM_ALL, 'scheme' => 'world', 'id' => 'anyone']];

    /**
     * @param string $host The host to connect to Zookeeper
     * @param string $path The base path to locks in Zookeeper
     * @throws RuntimeException
     */
    public function __construct(string $host, string $path = '/magento/locks')
    {
        if (empty($path)) {
            throw new RuntimeException(
                new Phrase('The path needs to be a non-empty string.')
            );
        }

        $this->host = $host;
        $this->path = preg_replace('#\/*$#', '', $path) ?: '/';
    }

    /**
     * {@inheritdoc}
     * @throws RuntimeException
     */
    public function lock(string $name, int $timeout = -1): bool
    {
        $skipDeadline = $timeout < 0;
        $lockPath = $this->getFullPathToLock($name);
        $deadline = microtime(true) + $timeout;

        while($this->getProvider()->exists($lockPath)) {
            if (!$skipDeadline && $deadline <= microtime(true)) {
                return false;
            }

            usleep($this->sleepCycle);
        }

        if (!$this->getProvider()->create($lockPath, '1', $this->acl, \Zookeeper::EPHEMERAL)) {
            throw new RuntimeException(new Phrase('Failed creating lock %1', [$lockPath]));
        } else {
            return true;
        }
    }

    /**
     * @inheritdoc
     */
    public function unlock(string $name): bool
    {
        $lockPath = $this->getFullPathToLock($name);

        if (!$this->getProvider()->exists($lockPath)) {
            return true;
        }

        return $this->getProvider()->delete($lockPath);
    }

    /**
     * @inheritdoc
     */
    public function isLocked(string $name): bool
    {
        return (bool) $this->getProvider()->exists($this->getFullPathToLock($name));
    }

    /**
     * Gets full path to lock by its name
     *
     * @param string $name
     * @return string
     */
    private function getFullPathToLock(string $name): string
    {
        return $this->path . '/' . $name;
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

            $deadline = microtime(true) + $this->connectionTimeout;
            while($this->zookeeper->getState() != \Zookeeper::CONNECTED_STATE) {
                if ($deadline <= microtime(true)) {
                    throw new RuntimeException(new Phrase('Zookeeper connection timed out!'));
                }
                usleep($this->sleepCycle);
            }

            if (!$this->createBasePath($this->path)) {
                throw new RuntimeException(new Phrase('Failed creating base path %1', [$this->path]));
            }
        }

        return $this->zookeeper;
    }

    /**
     * Checks and creates base path recursively
     *
     * @param $path
     * @return bool
     */
    private function createBasePath($path)
    {
        if ($this->zookeeper->exists($path)) {
            return true;
        }

        if (!$this->createBasePath(dirname($path))) {
            return false;
        }

        if ($this->zookeeper->create($path, '1', $this->acl)) {
            return true;
        }

        return $this->zookeeper->exists($path);
    }
}
