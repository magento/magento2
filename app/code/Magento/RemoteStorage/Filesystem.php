<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\Filesystem as BaseFilesystem;
use Magento\RemoteStorage\Driver\DriverPool;
use Magento\RemoteStorage\Model\Config;
use Magento\RemoteStorage\Model\Filesystem\Directory\WriteFactory as RemoteStorageWriteFactory;

/**
 * Filesystem implementation for remote storage.
 */
class Filesystem extends BaseFilesystem implements FilesystemInterface
{
    /**
     * @var bool
     */
    private $isEnabled;

    /**
     * @var array
     */
    private $directoryCodes;

    /**
     * @var DriverPool
     */
    private $driverPool;

    /**
     * @var RemoteStorageWriteFactory
     */
    private $remoteStorageWriteFactory;

    /**
     * @param BaseFilesystem\DirectoryList $directoryList
     * @param ReadFactory $readFactory
     * @param WriteFactory $writeFactory
     * @param Config $config
     * @param DriverPool $driverPool
     * @param array $directoryCodes
     */
    public function __construct(
        BaseFilesystem\DirectoryList $directoryList,
        ReadFactory $readFactory,
        WriteFactory $writeFactory,
        Config $config,
        DriverPool $driverPool,
        array $directoryCodes = [],
        RemoteStorageWriteFactory $remoteStorageWriteFactory = null
    ) {
        $this->isEnabled = $config->isEnabled();
        $this->driverPool = $driverPool;
        $this->directoryCodes = $directoryCodes;
        $this->remoteStorageWriteFactory = $remoteStorageWriteFactory ?: ObjectManager::getInstance()->get(
            RemoteStorageWriteFactory::class
        );

        parent::__construct($directoryList, $readFactory, $writeFactory);
    }

    /**
     * @inheritDoc
     */
    public function getDirectoryRead($directoryCode, $driverCode = DriverPool::REMOTE)
    {
        $hasCode = !$this->directoryCodes || in_array($directoryCode, $this->directoryCodes, true);

        if ($driverCode === DriverPool::REMOTE && $hasCode && $this->isEnabled) {
            $code = $directoryCode . '_' . $driverCode;

            if (!array_key_exists($code, $this->readInstances)) {
                $uri = $this->getUri($directoryCode) ?: '';

                $this->readInstances[$code] = $this->readFactory->create(
                    $this->driverPool->getDriver()->getAbsolutePath('', $uri),
                    $driverCode
                );
            }

            return $this->readInstances[$code];
        }

        return parent::getDirectoryRead($directoryCode);
    }

    /**
     * @inheritDoc
     */
    public function getDirectoryWrite($directoryCode, $driverCode = DriverPool::REMOTE)
    {
        $hasCode = !$this->directoryCodes || in_array($directoryCode, $this->directoryCodes, true);

        if ($driverCode === DriverPool::REMOTE && $hasCode && $this->isEnabled) {
            $code = $directoryCode . '_' . $driverCode;

            if (!array_key_exists($code, $this->writeInstances)) {
                $uri = $this->getUri($directoryCode) ?: '';
                $remoteDirectoryWrite = $this->writeFactory->create(
                    $this->driverPool->getDriver()->getAbsolutePath('', $uri),
                    $driverCode
                );
                $localDirectoryWrite = parent::getDirectoryWrite($directoryCode);
                $this->writeInstances[$code] = $this->remoteStorageWriteFactory->create(
                    $remoteDirectoryWrite,
                    $localDirectoryWrite
                );
            }

            return $this->writeInstances[$code];
        }

        return parent::getDirectoryWrite($directoryCode);
    }

    /**
     * @inheritDoc
     */
    public function getDirectoryReadByPath($path, $driverCode = DriverPool::REMOTE)
    {
        if ($driverCode === DriverPool::REMOTE && $this->isEnabled) {
            return $this->readFactory->create(
                $this->driverPool->getDriver()->getAbsolutePath('', $path),
                $driverCode
            );
        }

        return parent::getDirectoryReadByPath($path);
    }

    /**
     * @inheritDoc
     */
    public function getDirectoryCodes(): array
    {
        return $this->directoryCodes;
    }
}
