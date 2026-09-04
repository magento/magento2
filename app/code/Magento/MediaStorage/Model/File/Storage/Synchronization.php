<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaStorage\Model\File\Storage;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Directory\WriteInterface as DirectoryWrite;
use Magento\Framework\Filesystem\File\WriteInterface;
use Magento\MediaStorage\Helper\File\Storage\Database as DatabaseStorageHelper;

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
     * @var DatabaseStorageHelper
     */
    private $databaseStorageHelper;

    /**
     * @param DatabaseFactory $storageFactory
     * @param DirectoryWrite $directory
     * @param ?DatabaseStorageHelper $databaseStorageHelper
     */
    public function __construct(
        DatabaseFactory $storageFactory,
        DirectoryWrite $directory,
        ?DatabaseStorageHelper $databaseStorageHelper = null,
    ) {
        $this->databaseStorageHelper = $databaseStorageHelper ??
            ObjectManager::getInstance()->get(DatabaseStorageHelper::class);

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
        if (!$this->databaseStorageHelper->checkDbUsage()) {
            return;
        }

        /** @var Database $storage */
        $storage = $this->storageFactory->create();
        try {
            $storage->loadByFilename($relativeFileName);
        } catch (\Exception $e) { // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
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
