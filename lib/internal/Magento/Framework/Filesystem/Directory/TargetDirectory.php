<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filesystem\Directory;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;

/**
 * A target directory for remote filesystems.
 */
class TargetDirectory
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $driverCode;

    /**
     * @param Filesystem $filesystem
     * @param string $driverCode
     */
    public function __construct(Filesystem $filesystem, $driverCode = Filesystem\DriverPool::FILE)
    {
        $this->filesystem = $filesystem;
        $this->driverCode = $driverCode;
    }

    /**
     * Create an instance of directory with write permissions.
     *
     * @param string $directoryCode
     * @return WriteInterface
     * @throws FileSystemException
     */
    public function getDirectoryWrite(string $directoryCode): WriteInterface
    {
        return $this->filesystem->getDirectoryWrite($directoryCode, $this->driverCode);
    }

    /**
     * Create an instance of directory with read permissions.
     *
     * @param string $directoryCode
     * @return ReadInterface
     */
    public function getDirectoryRead(string $directoryCode): ReadInterface
    {
        return $this->filesystem->getDirectoryRead($directoryCode, $this->driverCode);
    }
}
