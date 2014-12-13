<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Core\Model\File\Storage;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\WriteInterface as DirectoryWrite;
use Magento\Framework\Filesystem\File\Write;
use Magento\Framework\Filesystem\FilesystemException;

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
    protected $pubDirectory;

    /**
     * @param \Magento\Core\Model\File\Storage $storage
     * @param \Magento\Framework\Filesystem $filesystem
     * @param string $cacheFile
     */
    public function __construct(
        \Magento\Core\Model\File\Storage $storage,
        \Magento\Framework\Filesystem $filesystem,
        $cacheFile
    ) {
        $this->config = $storage->getScriptConfig();
        $this->pubDirectory = $filesystem->getDirectoryWrite(DirectoryList::PUB);
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
        $file = $this->pubDirectory->openFile($this->pubDirectory->getRelativePath($this->cacheFilePath), 'w');
        try {
            $file->lock();
            $file->write(json_encode($this->config));
            $file->unlock();
            $file->close();
        } catch (FilesystemException $e) {
            $file->close();
        }
    }
}
