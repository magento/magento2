<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryUi\Model\Directories;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\Filesystem\Glob;
use Magento\MediaGalleryApi\Api\IsPathExcludedInterface;

/**
 * Build media gallery folder tree structure
 */
class GetDirectoryTree
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var IsPathExcludedInterface
     */
    private $isPathExcluded;

    /**
     * @var Glob
     */
    private $glob;

    /**
     * @param Filesystem $filesystem
     * @param Glob $glob
     * @param IsPathExcludedInterface $isPathExcluded
     */
    public function __construct(
        Filesystem $filesystem,
        Glob $glob,
        IsPathExcludedInterface $isPathExcluded
    ) {
        $this->filesystem = $filesystem;
        $this->glob = $glob;
        $this->isPathExcluded = $isPathExcluded;
    }

    /**
     * Return directory folder structure in array
     *
     * @return array
     * @throws ValidatorException
     */
    public function execute(): array
    {
        return $this->getDirectories();
    }

    /**
     * Read media directories recursively and build directory tree array in the jstree format
     *
     * @param string $path
     * @return array
     * @throws ValidatorException
     */
    private function getDirectories(string $path = ''): array
    {
        $directories = [];

        $absolutePath = $this->getMediaDirectory()->getAbsolutePath($path);

        foreach ($this->glob->glob(rtrim($absolutePath, '/') . '/*', Glob::GLOB_ONLYDIR) as $childPath) {
            $relativePath = $this->getMediaDirectory()->getRelativePath($childPath);

            if ($this->isPathExcluded->execute($relativePath)) {
                $directory = $this->getDirectories($relativePath);

                if (!empty($directory)) {
                    $directories[] = current($directory);
                }
                continue;
            }

            $directories[] = $this->getTreeNode($relativePath);
        }

        return $directories;
    }

    /**
     * Format tree node based on path (relative to media directory)
     *
     * @param string $path
     * @return array
     * @throws ValidatorException
     */
    private function getTreeNode(string $path): array
    {
        $pathArray = explode('/', $path);

        return [
            'text' => count($pathArray) > 0 ? end($pathArray) : $path,
            'id' => $path,
            'li_attr' => ['data-id' => $path],
            'path' => $path,
            'path_array' => $pathArray,
            'children' => $this->getDirectories($path)
        ];
    }

    /**
     * Retrieve media directory with read access
     *
     * @return ReadInterface
     */
    private function getMediaDirectory(): ReadInterface
    {
        return $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
    }
}
