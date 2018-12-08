<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);
<<<<<<< HEAD
namespace Magento\Framework\App\Filesystem;

use Magento\Framework\Filesystem;
use Magento\Framework\App\ObjectManager;
=======

namespace Magento\Framework\App\Filesystem;

use Magento\Framework\Filesystem;
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3

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
<<<<<<< HEAD
     * @param Filesystem|null $filesystem
     * @throws \RuntimeException
     */
    public function __construct(DirectoryList $directoryList, Filesystem $filesystem = null)
    {
        $this->directoryList = $directoryList;
        $this->filesystem = $filesystem ?: ObjectManager::getInstance()->get(Filesystem::class);
=======
     * @param Filesystem $filesystem
     */
    public function __construct(DirectoryList $directoryList, Filesystem $filesystem)
    {
        $this->directoryList = $directoryList;
        $this->filesystem = $filesystem;
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
<<<<<<< HEAD
        
=======

>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
        return strpos($realPath, $root) === 0;
    }
}
