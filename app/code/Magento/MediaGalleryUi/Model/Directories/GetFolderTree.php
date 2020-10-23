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
use Magento\MediaGalleryApi\Api\IsPathExcludedInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\Io\File as FileIo;

/**
 * Build media gallery folder tree structure by path
 */
class GetFolderTree
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
     * @var File
     */
    private $driver;

    /**
     * @var FileIo
     */
    private $file;

    /**
     * @param Filesystem $filesystem
     * @param File $driver
     * @param FileIo $file
     * @param IsPathExcludedInterface $isPathExcluded
     */
    public function __construct(
        Filesystem $filesystem,
        File $driver,
        FileIo $file,
        IsPathExcludedInterface $isPathExcluded
    ) {
        $this->filesystem = $filesystem;
        $this->driver = $driver;
        $this->file = $file;
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
        $tree = [
            'name' => 'root',
            'path' => '/',
            'children' => []
        ];
        $directories = $this->getDirectories();
        foreach ($directories as $idx => &$node) {
            $node['children'] = [];
            $result = $this->findParent($node, $tree);
            $parent = &$result['treeNode'];

            $parent['children'][] = &$directories[$idx];
        }
        return $tree['children'];
    }

    /**
     * Build directory tree array in format for jstree strandart
     *
     * @return array
     * @throws ValidatorException
     */
    private function getDirectories(): array
    {
        $directories = [];

        $mediaPath = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        $directoryList = $this->recursiveRead($mediaPath . '*', GLOB_BRACE | GLOB_ONLYDIR);

        foreach ($directoryList as $path) {
            $path = str_replace($mediaPath, '', $path);

            if ($this->isPathExcluded->execute($path)) {
                continue;
            }

            $pathArray = explode('/', $path);
            $directories[] = [
                'data' => count($pathArray) > 0 ? end($pathArray) : $path,
                'attr' => ['id' => $path],
                'metadata' => [
                    'path' => $path
                ],
                'path_array' => $pathArray
            ];
        }
        return $directories;
    }

    /**
     * Read only directories from file system
     *
     * @param string $pattern
     * @param int $flags
     */
    private function recursiveRead(string $pattern, int $flags = 0): array
    {
        $directories = glob($pattern, $flags);

        foreach (glob($this->driver->getParentDirectory($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
            $directories = array_merge(
                $directories,
                $this->recursiveRead($dir.'/'.  $this->file->getPathInfo($pattern)['basename'], $flags)
            );
        }

        return $directories;
    }

    /**
     * Find parent directory
     *
     * @param array $node
     * @param array $treeNode
     * @param int $level
     * @return array
     */
    private function findParent(array &$node, array &$treeNode, int $level = 0): array
    {
        $nodePathLength = count($node['path_array']);
        $treeNodeParentLevel = $nodePathLength - 1;

        $result = ['treeNode' => &$treeNode];

        if ($nodePathLength <= 1 || $level > $treeNodeParentLevel) {
            return $result;
        }

        foreach ($treeNode['children'] as &$tnode) {
            if ($node['path_array'][$level] === $tnode['path_array'][$level]) {
                return $this->findParent($node, $tnode, $level + 1);
            }
        }
        return $result;
    }
}
