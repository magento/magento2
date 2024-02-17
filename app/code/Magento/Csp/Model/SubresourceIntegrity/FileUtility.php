<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\SubresourceIntegrity;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\View\Asset\AssetInterface;
use Magento\Framework\View\Asset\LocalInterface;

/**
 * Class contains utility function for file
 */
class FileUtility
{

    /**
     * @var Filesystem
     */
    private Filesystem $filesystem;

    /**
     * constructor
     *
     * @param Filesystem $filesystem
     */
    public function __construct(
        Filesystem $filesystem
    ) {
        $this->filesystem = $filesystem;
    }

    /**
     * Check if file exists
     *
     * @param string $path
     * @return bool
     * @throws FileSystemException
     */
    public function checkFileExists(string $path): bool
    {
        $dir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);
        return $dir->isExist($path);
    }

    /**
     * Get file content for local asset from file system
     *
     * @param AssetInterface $asset
     * @return string
     * @throws FileSystemException
     */
    public function getFileContents(AssetInterface $asset): string
    {
        $path = $asset instanceof LocalInterface ? $asset->getpath() : '';
        $fileContent = '';

        if ($path && $this->checkFileExists($path)) {
            $fileContent = $asset->getContent();
        }
        return $fileContent;
    }
}
