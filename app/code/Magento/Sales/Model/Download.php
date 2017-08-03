<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class \Magento\Sales\Model\Download
 *
 */
class Download
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_rootDir;

    /**
     * @var \Magento\MediaStorage\Helper\File\Storage\Database
     */
    protected $_fileStorageDatabase;

    /**
     * @var \Magento\MediaStorage\Model\File\Storage\DatabaseFactory
     */
    protected $_storageDatabaseFactory;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @var string
     */
    protected $rootDirBasePath;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDatabase
     * @param \Magento\MediaStorage\Model\File\Storage\DatabaseFactory $storageDatabaseFactory
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param string $rootDirBasePath
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDatabase,
        \Magento\MediaStorage\Model\File\Storage\DatabaseFactory $storageDatabaseFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        $rootDirBasePath = DirectoryList::MEDIA
    ) {
        $this->rootDirBasePath = $rootDirBasePath;
        $this->_rootDir = $filesystem->getDirectoryWrite($this->rootDirBasePath);
        $this->_fileStorageDatabase = $fileStorageDatabase;
        $this->_storageDatabaseFactory = $storageDatabaseFactory;
        $this->_fileFactory = $fileFactory;
    }

    /**
     * Custom options downloader
     *
     * @param array $info
     * @return void
     * @throws \Exception
     */
    public function downloadFile($info)
    {
        $relativePath = $info['order_path'];
        if (!$this->_isCanProcessed($relativePath)) {
            //try get file from quote
            $relativePath = $info['quote_path'];
            if (!$this->_isCanProcessed($relativePath)) {
                throw new LocalizedException(
                    __('Path "%1" is not part of allowed directory "%2"', $relativePath, $this->rootDirBasePath)
                );
            }
        }
        $this->_fileFactory->create(
            $info['title'],
            ['value' => $this->_rootDir->getRelativePath($relativePath), 'type' => 'filename'],
            $this->rootDirBasePath
        );
    }

    /**
     * @param string $relativePath
     * @return bool
     */
    protected function _isCanProcessed($relativePath)
    {
        $filePath = $this->_rootDir->getAbsolutePath($relativePath);
        $pathWithFixedSeparator = str_replace('\\', '/', $this->_rootDir->getDriver()->getRealPath($filePath));
        return (strpos($pathWithFixedSeparator, $relativePath) !== false
            && $this->_rootDir->isFile($relativePath) && $this->_rootDir->isReadable($relativePath))
            || $this->_processDatabaseFile($filePath, $relativePath);
    }

    /**
     * Check file in database storage if needed and place it on file system
     *
     * @param string $filePath
     * @param string $relativePath
     * @return bool
     */
    protected function _processDatabaseFile($filePath, $relativePath)
    {
        if (!$this->_fileStorageDatabase->checkDbUsage()) {
            return false;
        }
        $file = $this->_storageDatabaseFactory->create()->loadByFilename($relativePath);
        if (!$file->getId()) {
            return false;
        }
        $stream = $this->_rootDir->openFile($relativePath, 'w+');
        $stream->lock();
        $stream->write($filePath, $file->getContent());
        $stream->unlock();
        $stream->close();
        return true;
    }
}
