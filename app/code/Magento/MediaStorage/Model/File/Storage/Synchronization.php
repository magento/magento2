<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaStorage\Model\File\Storage;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Directory\WriteInterface as DirectoryWrite;
use Magento\Framework\Filesystem\File\WriteInterface;

/**
 * Synchronize files from Db storage to local file system
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
     * @param DatabaseFactory $storageFactory
     * @param DirectoryWrite $directory
     */
    public function __construct(
        DatabaseFactory $storageFactory,
        DirectoryWrite $directory
    ) {
        $this->storageFactory = $storageFactory;
        $this->mediaDirectory = $directory;
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
