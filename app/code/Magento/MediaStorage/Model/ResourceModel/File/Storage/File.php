<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Model\ResourceModel\File\Storage;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem\Io\File as FileIo;
use Magento\Framework\App\ObjectManager;

/**
 * Class File
 */
class File
{
    /**
     * @var FileIo
     */
    private $fileIo;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Psr\Log\LoggerInterface $log
     * @param FileIo $fileIo
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Psr\Log\LoggerInterface $log,
        ?FileIo $fileIo = null
    ) {
        $this->_logger = $log;
        $this->_filesystem = $filesystem;
        $this->fileIo = $fileIo ?? ObjectManager::getInstance()->get(FileIo::class);
    }

    /**
     * Collect files and directories recursively
     *
     * @param string $dir
     * @return array
     */
    public function getStorageData($dir = '/')
    {
        $files = [];
        $directories = [];
        $directoryInstance = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
        if ($directoryInstance->isDirectory($dir)) {
            foreach ($directoryInstance->readRecursively($dir) as $path) {
                $pathInfo = $this->fileIo->getPathInfo($path);
                $itemName = $pathInfo['basename'];
                if ($itemName == '.svn' || $itemName == '.htaccess') {
                    continue;
                }
                if ($directoryInstance->isDirectory($path)) {
                    $directories[] = [
                        'name' => $itemName,
                        'path' => $pathInfo['dirname'] === '.' ? '/' : $pathInfo['dirname'],
                    ];
                } else {
                    $files[] = $path;
                }
            }
        }

        return ['files' => $files, 'directories' => $directories];
    }

    /**
     * Clear all files in storage $dir
     *
     * @param string $dir
     * @return $this
     */
    public function clear($dir = '')
    {
        $directoryInstance = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        if ($directoryInstance->isDirectory($dir)) {
            $paths = $directoryInstance->readRecursively($dir);
            foreach ($paths as $path) {
                if ($directoryInstance->isDirectory($path)) {
                    continue;
                }

                $pathInfo = $this->fileIo->getPathInfo($path);

                if ($pathInfo['basename'] !== '.htaccess') {
                    $directoryInstance->delete($path);
                }
            }
        }

        return $this;
    }

    /**
     * Save directory to storage
     *
     * @param array $dir
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return bool
     */
    public function saveDir($dir)
    {
        if (!isset($dir['name']) || !strlen($dir['name']) || !isset($dir['path'])) {
            return false;
        }

        $path = strlen($dir['path']) ? $dir['path'] . '/' . $dir['name'] : $dir['name'];

        try {
            $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA)->create($path);
        } catch (\Exception $e) {
            $this->_logger->info($e->getMessage());
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Unable to create directory: %1', DirectoryList::MEDIA . '/' . $path)
            );
        }

        return true;
    }

    /**
     * Save file to storage
     *
     * @param string $filePath
     * @param string $content
     * @param bool $overwrite
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return bool
     */
    public function saveFile($filePath, $content, $overwrite = false)
    {
        try {
            $directoryInstance = $this->_filesystem->getDirectoryWrite(DirectoryList::MEDIA);
            if (!$directoryInstance->isFile($filePath) || $overwrite && $directoryInstance->delete($filePath)) {
                $directoryInstance->writeFile($filePath, $content);
                return true;
            }
        } catch (\Magento\Framework\Exception\FileSystemException $e) {
            $this->_logger->info($e->getMessage());
            throw new LocalizedException(__('Unable to save file: %1', $filePath));
        }

        return false;
    }
}
