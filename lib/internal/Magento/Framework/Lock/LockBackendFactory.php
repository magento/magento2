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
     * The list of lock providers with mapping on classes
     *
     * @var array
     */
    private $lockers = [
        self::LOCK_DB => DatabaseLock::class,
        self::LOCK_ZOOKEEPER => ZookeeperLock::class
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
        $provider = $this->deploymentConfig->get('locks/provider', self::LOCK_DB);
        $config = $this->deploymentConfig->get('locks/config', []);

        if (!isset($this->lockers[$provider])) {
            throw new RuntimeException(new Phrase('Unknown locks provider.'));
        }

        return $this->objectManager->create($this->lockers[$provider], $config);
    }
}
