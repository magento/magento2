<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Lock;

use Magento\Framework\Exception\RuntimeException;

/**
 * Proxy for LockManagers
 */
class Proxy implements LockManagerInterface
{
    /**
     * The factory to create LockManagerInterface implementation
     *
     * @var LockBackendFactory
     */
    private $factory;

    /**
     * A LockManagerInterface implementation
     *
     * @var LockManagerInterface
     */
    private $locker;

    /**
     * @param LockBackendFactory $factory The factory to create LockManagerInterface implementation
     */
    public function __construct(LockBackendFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @inheritdoc
     *
     * @throws RuntimeException
     */
    public function isLocked(string $name): bool
    {
        return $this->getLocker()->isLocked($name);
    }

    /**
     * @inheritdoc
     *
     * @throws RuntimeException
     */
    public function lock(string $name, int $timeout = -1): bool
    {
        return $this->getLocker()->lock($name, $timeout);
    }

    /**
     * @inheritdoc
     *
     * @throws RuntimeException
     */
    public function unlock(string $name): bool
    {
        return $this->getLocker()->unlock($name);
    }

    /**
     * Gets LockManagerInterface implementation using Factory
     *
     * @return LockManagerInterface
     * @throws RuntimeException
     */
    private function getLocker(): LockManagerInterface
    {
        if (!$this->locker) {
            $this->locker = $this->factory->create();
        }

        return $this->locker;
    }
}
