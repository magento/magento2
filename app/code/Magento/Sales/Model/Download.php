<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model;

use Magento\Framework\App\Filesystem\DirectoryList;

class Download
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $_rootDir;

    /**
     * @var \Magento\Core\Helper\File\Storage\Database
     */
    protected $_fileStorageDatabase;

    /**
     * @var \Magento\Core\Model\File\Storage\DatabaseFactory
     */
    protected $_storageDatabaseFactory;

    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $_fileFactory;

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Core\Helper\File\Storage\Database $fileStorageDatabase
     * @param \Magento\Core\Model\File\Storage\DatabaseFactory $storageDatabaseFactory
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Core\Helper\File\Storage\Database $fileStorageDatabase,
        \Magento\Core\Model\File\Storage\DatabaseFactory $storageDatabaseFactory,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory
    ) {
        $this->_rootDir = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
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
        if ($this->_isCanProcessed($relativePath)) {
            //try get file from quote
            $relativePath = $info['quote_path'];
            if ($this->_isCanProcessed($relativePath)) {
                throw new \Exception();
            }
        }

        $this->_fileFactory->create(
            $info['title'],
            ['value' => $this->_rootDir->getRelativePath($relativePath), 'type' => 'filename'],
            DirectoryList::ROOT
        );
    }

    /**
     * @param string $relativePath
     * @return bool
     */
    protected function _isCanProcessed($relativePath)
    {
        $filePath = $this->_rootDir->getAbsolutePath($relativePath);
        return (!$this->_rootDir->isFile(
            $relativePath
        ) || !$this->_rootDir->isReadable(
            $relativePath
        )) && !$this->_processDatabaseFile(
            $filePath
        );
    }

    /**
     * Check file in database storage if needed and place it on file system
     *
     * @param string $filePath
     * @return bool
     */
    protected function _processDatabaseFile($filePath)
    {
        if (!$this->_fileStorageDatabase->checkDbUsage()) {
            return false;
        }
        $relativePath = $this->_fileStorageDatabase->getMediaRelativePath($filePath);
        $file = $this->_storageDatabaseFactory->create()->loadByFilename($relativePath);
        if (!$file->getId()) {
            return false;
        }
        $stream = $this->_rootDir->openFile($filePath, 'w+');
        $stream->lock();
        $stream->write($filePath, $file->getContent());
        $stream->unlock();
        $stream->close();
        return true;
    }
}
