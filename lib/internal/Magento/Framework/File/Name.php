<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\File;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use \Magento\Framework\Filesystem\DriverInterface;

/**
 * Utility for generating a unique file name
 */
class Name
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param Filesystem|null $filesystem
     */
    public function __construct(Filesystem $filesystem = null)
    {
        $this->filesystem = $filesystem ?: ObjectManager::getInstance()->get(Filesystem::class);
    }

    /**
     * Gets new file name if the given name is in use
     *
     * @param string $destinationFile
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getNewFileName(string $destinationFile)
    {
        $fileInfo = $this->getPathInfo($destinationFile);
        $driver = $this->filesystem->getDirectoryWrite(
            DirectoryList::ROOT,
            Filesystem\DriverPool::FILE
        )->getDriver();

        if ($driver->isExists($destinationFile)) {
            return $this->generateFileName($driver, $fileInfo);
        }

        /**
         * Try with non-local driver.
         */
        $driver = $this->filesystem->getDirectoryWrite(DirectoryList::ROOT)->getDriver();

        return $driver->isExists($destinationFile)
            ? $this->generateFileName($driver, $fileInfo)
            : $fileInfo['basename'];
    }

    /**
     * Generates new file name until file with provided name doesn't exist
     *
     * @param DriverInterface $driver
     * @param string $fileInfo
     * @param int $index
     * @return string
     * @throws FileSystemException
     */
    private function generateFileName($driver, $fileInfo, $index = 1)
    {
        $baseName = $fileInfo['filename'] . '_' . $index . '.' . $fileInfo['extension'];
        if ($driver->isExists($fileInfo['dirname'] . '/' . $baseName)) {
            return $this->generateFileName($driver, $fileInfo, ++$index);
        }
        return $baseName;
    }

    /**
     * Gets the path information from a given file
     *
     * @param string $destinationFile
     * @return string|string[]
     */
    private function getPathInfo(string $destinationFile)
    {
        return pathinfo($destinationFile);
    }
}
