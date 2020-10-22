<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Plugin;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\MediaStorage\Model\File\Storage\Synchronization;
use Magento\RemoteStorage\Driver\DriverPool as RemoteDriverPool;
use Magento\Framework\Filesystem\DriverPool as LocalDriverPool;
use Magento\RemoteStorage\Model\Config;
use Magento\RemoteStorage\Filesystem;

/**
 * Modifies the base URL.
 */
class MediaStorage
{
    /**
     * @var bool
     */
    private $isEnabled;

    /**
     * @var WriteInterface
     */
    private $remoteDir;

    /**
     * @var WriteInterface
     */
    private $localDir;

    /**
     * @param Config $config
     * @param Filesystem $filesystem
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function __construct(Config $config, Filesystem $filesystem)
    {
        $this->isEnabled = $config->isEnabled();
        $this->remoteDir = $filesystem->getDirectoryWrite(DirectoryList::PUB, RemoteDriverPool::REMOTE);
        $this->localDir = $filesystem->getDirectoryWrite(DirectoryList::PUB, LocalDriverPool::FILE);
    }

    /**
     * Download remote file
     * @param Synchronization $subject
     * @param string $relativeFileName
     * @return null
     * @throws FileSystemException
     * @throws ValidatorException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSynchronize(Synchronization $subject, string $relativeFileName)
    {
        if ($this->isEnabled) {
            if ($this->remoteDir->isExist($relativeFileName)) {
                $file = $this->localDir->openFile($relativeFileName, 'w');
                try {
                    $file->lock();
                    $file->write($this->remoteDir->readFile($relativeFileName));
                    $file->unlock();
                    $file->close();
                } catch (FileSystemException $e) {
                    $file->close();
                }
            }
        }
        return null;
    }
}
