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
use Magento\Framework\Filesystem\Directory\Read;
use Magento\MediaGalleryApi\Api\IsPathExcludedInterface;

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

        /** @var Read $directory */
        $directory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);

        if (!$directory->isDirectory()) {
            return $directories;
        }

        foreach ($directory->readRecursively() as $path) {
            if (!$directory->isDirectory($path) || $this->isPathExcluded->execute($path)) {
                continue;
            }

            $pathArray = explode('/', $path);
            $directories[] = [
                'text' => count($pathArray) > 0 ? end($pathArray) : $path,
                'id' => $path,
                'li_attr' => ['data-id' => $path],
                'path' => $path,
                'path_array' => $pathArray
            ];
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
            $tNodePathLength = count($tnode['path_array']);
            $found = false;
            while ($level < $tNodePathLength) {
                if ($node['path_array'][$level] === $tnode['path_array'][$level]) {
                    $level ++;
                    $found = true;
                } else {
                    break;
                }
            }
            if ($found) {
                return $this->findParent($node, $tnode, $level);
            }
        }
        return $result;
    }
}
