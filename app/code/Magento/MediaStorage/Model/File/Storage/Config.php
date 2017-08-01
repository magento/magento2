<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Model\File\Storage;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\WriteInterface as DirectoryWrite;
use Magento\Framework\Filesystem\File\Write;
use Magento\Framework\Exception\FileSystemException;

/**
 * Class \Magento\MediaStorage\Model\File\Storage\Config
 *
 * @since 2.0.0
 */
class Config
{
    /**
     * Config cache file path
     *
     * @var string
     * @since 2.0.0
     */
    protected $cacheFilePath;

    /**
     * Loaded config
     *
     * @var array
     * @since 2.0.0
     */
    protected $config;

    /**
     * File stream handler
     *
     * @var DirectoryWrite
     * @since 2.0.0
     */
    protected $rootDirectory;

    /**
     * @param \Magento\MediaStorage\Model\File\Storage $storage
     * @param \Magento\Framework\Filesystem $filesystem
     * @param string $cacheFile
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getMediaDirectory()
    {
        return $this->config['media_directory'];
    }

    /**
     * Retrieve list of allowed resources
     *
     * @return array
     * @since 2.0.0
     */
    public function getAllowedResources()
    {
        return $this->config['allowed_resources'];
    }

    /**
     * Save config in cache file
     *
     * @return void
     * @since 2.0.0
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
