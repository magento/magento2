<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\RemoteStorage\Model\Filesystem\Directory;

use Magento\Framework\Filesystem\Directory\CompositePathValidator;
use Magento\Framework\Filesystem\Directory\DenyListPathValidator;
use Magento\Framework\Filesystem\Directory\PathValidator;
use Magento\Framework\Filesystem\Directory\WriteFactory as BaseWriteFactory;
use Magento\Framework\Filesystem\DriverPool as BaseDriverPool;
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
     * @var BaseDriverPool
     */
    private $driverPool;

    /**
     * WriteFactory constructor.
     *
     * @param ObjectManagerInterface $objectManager
     * @param BaseDriverPool $driverPool
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        BaseDriverPool $driverPool
    ) {
        $this->objectManager = $objectManager;
        $this->driverPool = $driverPool;
        parent::__construct($driverPool);
    }

    /**
     * @inheritDoc
     */
    public function create(
        $path,
        $driverCode = DriverPool::REMOTE,
        $createPermissions = null,
        $directoryCode = null
    ) {
        if ($driverCode == DriverPool::REMOTE) {
            $driver = $this->driverPool->getDriver($driverCode);
            $factory = new \Magento\Framework\Filesystem\File\WriteFactory(
                $this->driverPool
            );

            $validators = [
                'pathValidator' => new PathValidator($driver),
                'denyListPathValidator' => new DenyListPathValidator($driver)
            ];

            $pathValidator = new CompositePathValidator($validators);
            return $this->objectManager->create(
                Write::class,
                [
                    'fileFactory' => $factory,
                    'driver' => $driver,
                    'path' => $path,
                    'createPermissions' => $createPermissions,
                    'pathValidator' => $pathValidator,
                    'directoryCode' => $directoryCode
                ]
            );
        } else {
            return parent::create($path, $driverCode, $createPermissions);
        }

    }
}
