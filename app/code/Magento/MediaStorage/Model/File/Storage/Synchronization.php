<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Model\File\Storage;

use Magento\Framework\Filesystem\Directory\WriteInterface as DirectoryWrite;
use Magento\Framework\Filesystem\File\Write;
use Magento\Framework\Exception\FileSystemException;

/**
 * Class Synchronization
 */
class Synchronization
{
    /**
     * Database storage factory
     *
     * @var \Magento\MediaStorage\Model\File\Storage\DatabaseFactory
     */
    protected $storageFactory;

    /**
     * File stream handler
     *
     * @var DirectoryWrite
     */
    protected $mediaDirectory;

    /**
     * @param \Magento\MediaStorage\Model\File\Storage\DatabaseFactory $storageFactory
     * @param DirectoryWrite $directory
     */
    public function __construct(
        \Magento\MediaStorage\Model\File\Storage\DatabaseFactory $storageFactory,
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
        /** @var $storage \Magento\MediaStorage\Model\File\Storage\Database */
        $storage = $this->storageFactory->create();
        try {
            $storage->loadByFilename($relativeFileName);
        } catch (\Exception $e) {
        }
        if ($storage->getId()) {
            /** @var Write $file */
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
