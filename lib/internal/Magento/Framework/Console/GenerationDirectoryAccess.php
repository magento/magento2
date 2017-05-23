<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Console;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Framework\Filesystem\DriverPool;
use Magento\Framework\Filesystem\File\WriteFactory;
use Magento\Framework\Filesystem\Directory\Write;
use Zend\ServiceManager\ServiceManager;
use Magento\Setup\Mvc\Bootstrap\InitParamListener;

/**
 * Check generated/code read and write access
 */
class GenerationDirectoryAccess
{
    /**
     * @var ServiceManager
     */
    private $serviceManager;

    /**
     * @param ServiceManager $serviceManager
     */
    public function __construct(
        ServiceManager $serviceManager
    ) {
        $this->serviceManager = $serviceManager;
    }

    /**
     * Check generated/code read and write access
     *
     * @return bool
     */
    public function check()
    {
        $initParams = $this->serviceManager->get(InitParamListener::BOOTSTRAP_PARAM);
        $filesystemDirPaths = isset($initParams[Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS])
            ? $initParams[Bootstrap::INIT_PARAM_FILESYSTEM_DIR_PATHS]
            : [];
        $directoryList = new DirectoryList(BP, $filesystemDirPaths);
        $driverPool = new DriverPool();
        $fileWriteFactory = new WriteFactory($driverPool);
        /** @var \Magento\Framework\Filesystem\DriverInterface $driver */
        $driver = $driverPool->getDriver(DriverPool::FILE);

        $generationDirs = [
            DirectoryList::GENERATED,
            DirectoryList::GENERATED_CODE,
            DirectoryList::GENERATED_METADATA
        ];

        foreach ($generationDirs as $generationDirectory) {
            $directoryPath = $directoryList->getPath($generationDirectory);

            if (!$this->checkDirectory($fileWriteFactory, $driver, $directoryPath)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks the permissions to specific directory
     *
     * @param WriteFactory $fileWriteFactory The factory of file writers
     * @param DriverInterface $driver The driver
     * @param string $directoryPath The directory path
     * @return bool
     */
    private function checkDirectory(
        WriteFactory $fileWriteFactory,
        DriverInterface $driver,
        $directoryPath
    ) {
        $directoryWrite = new Write($fileWriteFactory, $driver, $directoryPath);

        if (!$directoryWrite->isExist()) {
            try {
                $directoryWrite->create();
            } catch (\Exception $e) {
                return false;
            }
        }

        if (!$directoryWrite->isDirectory() || !$directoryWrite->isReadable()) {
            return false;
        }

        try {
            $probeFilePath = $directoryPath . DIRECTORY_SEPARATOR . uniqid(mt_rand()) . 'tmp';
            $fileWriteFactory->create($probeFilePath, DriverPool::FILE, 'w');
            $driver->deleteFile($probeFilePath);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
