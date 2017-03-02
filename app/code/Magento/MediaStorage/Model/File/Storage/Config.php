<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Model\File\Storage;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\WriteInterface as DirectoryWrite;
use Magento\Framework\Filesystem\File\Write;
use Magento\Framework\Exception\FileSystemException;

class Config
{
    /**
     * Config cache file path
     *
     * @var string
     */
    protected $cacheFilePath;

    /**
     * Loaded config
     *
     * @var array
     */
    protected $config;

    /**
     * File stream handler
     *
     * @var DirectoryWrite
     */
    protected $rootDirectory;

    /**
     * @param \Magento\MediaStorage\Model\File\Storage $storage
     * @param \Magento\Framework\Filesystem $filesystem
     * @param string $cacheFile
     */
    public function __construct(
        \Magento\MediaStorage\Model\File\Storage $storage,
        \Magento\Framework\Filesystem $filesystem,
        $cacheFile
    ) {
        $this->config = $storage->getScriptConfig();
        $this->rootDirectory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->cacheFilePath = $cacheFile;
    }

    /**
     * Retrieve media directory
     *
     * @return string
     */
    public function getMediaDirectory()
    {
        return $this->config['media_directory'];
    }

    /**
     * Retrieve list of allowed resources
     *
     * @return array
     */
    public function getAllowedResources()
    {
        return $this->config['allowed_resources'];
    }

    /**
     * Save config in cache file
     *
     * @return void
     */
    public function save()
    {
        /** @var Write $file */
        $file = $this->rootDirectory->openFile($this->rootDirectory->getRelativePath($this->cacheFilePath), 'w');
        try {
            $file->lock();
            $file->write(json_encode($this->config));
            $file->unlock();
            $file->close();
        } catch (FileSystemException $e) {
            $file->close();
        }
    }
}
