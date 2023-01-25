<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Console;

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\WriteFactory;
use Magento\Framework\Filesystem\DriverPool;
use Laminas\ServiceManager\ServiceManager;
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
     * Check write permissions to generation folders
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

        $generationDirs = [
            DirectoryList::GENERATED,
            DirectoryList::GENERATED_CODE,
            DirectoryList::GENERATED_METADATA
        ];

        foreach ($generationDirs as $generationDirectory) {
            $directoryPath = $directoryList->getPath($generationDirectory);
            $directoryWrite = $fileWriteFactory->create($directoryPath);

            if (!$directoryWrite->isExist()) {
                try {
                    $directoryWrite->create();
                } catch (\Exception $e) {
                    return false;
                }
            }

            if (!$directoryWrite->isWritable()) {
                return false;
            }
        }

        return true;
    }
}
