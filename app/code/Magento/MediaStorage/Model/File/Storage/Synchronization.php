<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Model\File\Storage;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Directory\WriteInterface as DirectoryWrite;
use Magento\Framework\Filesystem\File\WriteInterface;
use Magento\MediaStorage\Service\ImageResize;
use Magento\MediaStorage\Model\File\Storage\Database;
use Psr\Log\LoggerInterface;

/**
 * Class Synchronization
 */
class Synchronization
{
    /**
     * Database storage factory
     *
     * @var DatabaseFactory
     */
    protected $storageFactory;

    /**
     * File stream handler
     *
     * @var DirectoryWrite
     */
    protected $mediaDirectory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param DatabaseFactory $storageFactory
     * @param DirectoryWrite $directory
     * @param LoggerInterface $logger
     */
    public function __construct(
        DatabaseFactory $storageFactory,
        DirectoryWrite $directory,
        LoggerInterface $logger
    ) {
        $this->storageFactory = $storageFactory;
        $this->mediaDirectory = $directory;
        $this->logger = $logger;
    }

    /**
     * Synchronize file
     *
     * @param string $relativeFileName
     * @return void
     * @throws \LogicException
     */
    public function synchronize($relativeFileName)
    {
        /** @var $storage Database */
        $storage = $this->storageFactory->create();
        try {
            $storage->loadByFilename($relativeFileName);
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
        if ($storage->getId()) {
            /** @var WriteInterface $file */
            $file = $this->mediaDirectory->openFile($relativeFileName, 'w');
            try {
                $file->lock();
                $file->write($storage->getContent());
                $file->unlock();
                $file->close();
            } catch (FileSystemException $e) {
                $file->close();
            }
        }
    }
}
