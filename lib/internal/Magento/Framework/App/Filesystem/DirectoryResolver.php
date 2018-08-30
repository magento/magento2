<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Filesystem;

use Magento\Framework\Filesystem;

/**
 * Magento directories resolver.
 */
class DirectoryResolver
{
    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @param DirectoryList $directoryList
     * @param Filesystem $filesystem
     */
    public function __construct(DirectoryList $directoryList, Filesystem $filesystem)
    {
        $this->directoryList = $directoryList;
        $this->filesystem = $filesystem;
    }

    /**
     * Validate path.
     *
     * Gets real path for directory provided in parameters and compares it with specified root directory.
     * Will return TRUE if real path of provided value contains root directory path and FALSE if not.
     * Throws the \Magento\Framework\Exception\FileSystemException in case when directory path is absent
     * in Directories configuration.
     *
     * @param string $path
     * @param string $directoryConfig
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function validatePath($path, $directoryConfig = DirectoryList::MEDIA)
    {
        $directory = $this->filesystem->getDirectoryWrite($directoryConfig);
        $realPath = $directory->getDriver()->getRealPathSafety($path);
        $root = $this->directoryList->getPath($directoryConfig);

        return strpos($realPath, $root) === 0;
    }
}
