<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Lock;

use Magento\Framework\Phrase;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Lock\Backend\Database as DatabaseLock;
use Magento\Framework\Lock\Backend\Zookeeper as ZookeeperLock;
use Magento\Framework\Lock\Backend\Cache as CacheLock;
use Magento\Framework\Lock\Backend\FileLock;

/**
 * The factory to create object that implements LockManagerInterface
 */
class LockBackendFactory
{
    /**
     * The Object Manager instance
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * The Application deployment configuration
     *
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    /**
     * DB lock provider name
     *
     * @const string
     */
    const LOCK_DB = 'db';

    /**
     * Zookeeper lock provider name
     *
     * @const string
     */
    const LOCK_ZOOKEEPER = 'zookeeper';

    /**
     * Cache lock provider name
     *
     * @const string
     */
    const LOCK_CACHE = 'cache';

    /**
     * File lock provider name
     *
     * @const string
     */
    const LOCK_FILE = 'file';

    /**
     * The list of lock providers with mapping on classes
     *
     * @var array
     */
    private $lockers = [
        self::LOCK_DB => DatabaseLock::class,
        self::LOCK_ZOOKEEPER => ZookeeperLock::class,
        self::LOCK_CACHE => CacheLock::class,
        self::LOCK_FILE => FileLock::class,
    ];

    /**
     * @param ObjectManagerInterface $objectManager The Object Manager instance
     * @param DeploymentConfig $deploymentConfig The Application deployment configuration
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        DeploymentConfig $deploymentConfig
    ) {
        $this->objectManager = $objectManager;
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * Creates an instance of LockManagerInterface using information from deployment config
     *
     * @return LockManagerInterface
     * @throws RuntimeException
     */
    public function create(): LockManagerInterface
    {
        $provider = $this->deploymentConfig->get('lock/provider', self::LOCK_DB);
        $config = $this->deploymentConfig->get('lock/config', []);

        if (!isset($this->lockers[$provider])) {
            throw new RuntimeException(new Phrase('Unknown locks provider: %1', [$provider]));
        }

        if (self::LOCK_ZOOKEEPER === $provider && !extension_loaded(self::LOCK_ZOOKEEPER)) {
            throw new RuntimeException(new Phrase('php extension Zookeeper is not installed.'));
        }

        return $this->objectManager->create($this->lockers[$provider], $config);
    }
}
