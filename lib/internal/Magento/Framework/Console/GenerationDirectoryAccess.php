<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Console;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
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
        $generationDirectoryPath = $directoryList->getPath(DirectoryList::GENERATION);
        $driverPool = new DriverPool();
        $fileWriteFactory = new WriteFactory($driverPool);
        /** @var \Magento\Framework\Filesystem\DriverInterface $driver */
        $driver = $driverPool->getDriver(DriverPool::FILE);
        $directoryWrite = new Write($fileWriteFactory, $driver, $generationDirectoryPath);
        if ($directoryWrite->isExist()) {
            if ($directoryWrite->isDirectory()
                || $directoryWrite->isReadable()
            ) {
                try {
                    $probeFilePath = $generationDirectoryPath . DIRECTORY_SEPARATOR . uniqid(mt_rand()).'tmp';
                    $fileWriteFactory->create($probeFilePath, DriverPool::FILE, 'w');
                    $driver->deleteFile($probeFilePath);
                } catch (\Exception $e) {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            try {
                $directoryWrite->create();
            } catch (\Exception $e) {
                return false;
            }
        }
        return true;
    }
}
