<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Model\Directories;

use Exception;
use FilesystemIterator;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\Framework\Phrase;
use Magento\MediaGalleryApi\Api\IsPathExcludedInterface;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIterator;
use RecursiveIteratorIterator;
use SplFileInfo;
use function count;
use function explode;
use function strcmp;

/**
 * Build media gallery folder tree structure by path
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
     * @param Filesystem $filesystem
     * @param IsPathExcludedInterface $isPathExcluded
     */
    public function __construct(
        Filesystem $filesystem,
        IsPathExcludedInterface $isPathExcluded
    ) {
        $this->filesystem = $filesystem;
        $this->isPathExcluded = $isPathExcluded;
    }

    /**
     * Return directory folder structure in array
     *
     * @return array
     * @throws FileSystemException
     */
    public function execute(): array
    {
        $tree = ['children' => []];
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
     * @param string|null $path
     * @param int $depth
     * @return array
     * @throws FileSystemException
     */
    private function getDirectories(?string $path = null, int $depth = -1): array
    {
        $directories = [];

        /** @var Read $directory */
        $directory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);

        $isExcluded = function (string $path) use ($directory): bool {
            return $this->isPathExcluded->execute($directory->getRelativePath($path));
        };
        $flags = FilesystemIterator::SKIP_DOTS |
            FilesystemIterator::UNIX_PATHS |
            RecursiveDirectoryIterator::FOLLOW_SYMLINKS;

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveCallbackFilterIterator(
                    new RecursiveDirectoryIterator($directory->getAbsolutePath($path), $flags),
                    static function (SplFileInfo $file, $key, RecursiveIterator $iterator) use ($isExcluded): bool {
                        return $file->isDir() && $iterator->hasChildren() && !$isExcluded($file->getPathname());
                    }
                ),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            $iterator->setMaxDepth($depth);

            /** @var FilesystemIterator $file */
            foreach ($iterator as $file) {
                $path = $directory->getRelativePath($file->getPathname());
                $directories[] = [
                    'data' => $file->getBasename(),
                    'attr' => ['id' => $file->getPathname()],
                    'metadata' => [
                        'path' => $path
                    ],
                    'path_array' => explode('/', $path)
                ];
            }
        } catch (Exception $e) {
            throw new FileSystemException(new Phrase($e->getMessage()), $e);
        }
        usort($directories, static function (array $itemA, array $itemB): int {
            return strcmp($itemA['metadata']['path'], $itemB['metadata']['path']);
        });

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
