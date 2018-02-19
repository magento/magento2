<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Filesystem;

/**
 * Magento directories resolver
 */
class DirectoryResolver
{
    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directoryList;

    /**
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     */
    public function __construct(
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList
    ) {
        $this->directoryList = $directoryList;
    }

    /**
     * Validate path
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
        $realPath = realpath($path);
        $root = $this->directoryList->getPath($directoryConfig);
        return strpos($realPath, $root) === 0;
    }
}
