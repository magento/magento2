<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\RemoteStorage\Model\Filesystem\Directory;

use Magento\Framework\Filesystem\Directory\PathValidator;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Directory\WriteFactory as BaseWriteFactory;
use Magento\RemoteStorage\Driver\DriverPool;
use Magento\Framework\ObjectManagerInterface;

/**
 * The factory of the filesystem directory instances for remote storage write operations.
 */
class WriteFactory extends BaseWriteFactory
{
    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Pool of filesystem drivers
     *
     * @var DriverPool
     */
    private $driverPool;

    /**
     * WriteFactory constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param DriverPool $driverPool
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        DriverPool $driverPool
    ) {
        $this->objectManager = $objectManager;
        $this->driverPool = $driverPool;
        parent::__construct($driverPool);
    }

    /**
     * Create a remote storage write instance
     *
     * @param string $path
     * @param string $driverCode
     * @param string|null $createPermissions
     * @return \Magento\Framework\Filesystem\Directory\Write
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     * @throws \Magento\RemoteStorage\Driver\DriverException
     */
    public function create($path, $driverCode = DriverPool::REMOTE, $createPermissions = null)
    {
        if ($driverCode == DriverPool::REMOTE) {
            $driver = $this->driverPool->getDriver($driverCode);
            $factory = new \Magento\Framework\Filesystem\File\WriteFactory(
                $this->driverPool
            );
            return $this->objectManager->create(
                Write::class,
                [
                    'fileFactory' => $factory,
                    'driver' => $driver,
                    'path' => $path,
                    'createPermissions' => $createPermissions,
                    'pathValidator' => new PathValidator($driver),
                ]
            );
        } else {
            return parent::create($path, $driverCode, $createPermissions);
        }

    }
}
