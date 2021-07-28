<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\RemoteStorage\Model\Filesystem\Directory;

use Magento\Framework\Filesystem\Directory\WriteInterface;

/**
 * The factory of the filesystem directory instances for remote storage write operations.
 */
class WriteFactory
{
    /**
     * Create a remote storage writable directory
     *
     * @param WriteInterface $remoteDirectoryWrite
     * @param WriteInterface $localDirectoryWrite
     * @return Write
     */
    public function create(WriteInterface $remoteDirectoryWrite, WriteInterface $localDirectoryWrite)
    {
        return new Write($remoteDirectoryWrite, $localDirectoryWrite);
    }
}
