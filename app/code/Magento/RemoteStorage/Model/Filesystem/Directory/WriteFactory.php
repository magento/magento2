<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\RemoteStorage\Model\Filesystem\Directory;

use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * The factory of the filesystem directory instances for remote storage write operations.
 */
class WriteFactory
{
    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var string
     */
    private $instanceName;

    /**
     * Construct
     *
     * @param ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        string $instanceName = Write::class
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * Create a remote storage writable directory
     *
     * @param WriteInterface $remoteDirectoryWrite
     * @param WriteInterface $localDirectoryWrite
     * @return Write
     */
    public function create(WriteInterface $remoteDirectoryWrite, WriteInterface $localDirectoryWrite)
    {
        return $this->objectManager->create(
            $this->instanceName,
            [
                'remoteDirectoryWrite' => $remoteDirectoryWrite,
                'localDirectoryWrite' => $localDirectoryWrite]);
    }
}
