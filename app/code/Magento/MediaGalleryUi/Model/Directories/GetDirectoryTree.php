<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Model\Directories;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Read;
use Magento\MediaGalleryApi\Api\IsPathExcludedInterface;

/**
 * Build media gallery folder tree structure by path
 */
class GetDirectoryTree
{
    private const XML_PATH_MEDIA_GALLERY_IMAGE_FOLDERS
        = 'system/media_storage_configuration/allowed_resources/media_gallery_image_folders';
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var IsPathExcludedInterface
     */
    private $isPathExcluded;

    /**
     * @var ScopeConfigInterface
     */
    private $coreConfig;

    /**
     * @param Filesystem $filesystem
     * @param IsPathExcludedInterface $isPathExcluded
     * @param ScopeConfigInterface|null $coreConfig
     */
    public function __construct(
        Filesystem $filesystem,
        IsPathExcludedInterface $isPathExcluded,
        ?ScopeConfigInterface $coreConfig = null
    ) {
        $this->filesystem = $filesystem;
        $this->isPathExcluded = $isPathExcluded;
        $this->coreConfig = $coreConfig ?? ObjectManager::getInstance()->get(ScopeConfigInterface::class);
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

        /** @var Read $mediaDirectory */
        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);

        if ($mediaDirectory->isDirectory()) {
            $imageFolderPaths = $this->coreConfig->getValue(
                self::XML_PATH_MEDIA_GALLERY_IMAGE_FOLDERS,
                ScopeConfigInterface::SCOPE_TYPE_DEFAULT
            );
            sort($imageFolderPaths);

            foreach ($imageFolderPaths as $imageFolderPath) {
                $imageDirectory = $this->filesystem->getDirectoryReadByPath(
                    $mediaDirectory->getAbsolutePath($imageFolderPath)
                );
                if ($imageDirectory->isDirectory()) {
                    $directories[] = $this->getDirectoryData($imageFolderPath);
                    foreach ($imageDirectory->readRecursively() as $path) {
                        if ($imageDirectory->isDirectory($path)) {
                            $directories[] = $this->getDirectoryData(
                                $mediaDirectory->getRelativePath($imageDirectory->getAbsolutePath($path))
                            );
                        }
                    }
                }
            }
        }

        return $directories;
    }

    /**
     * Return jstree data for given path
     *
     * @param string $path
     * @return array
     */
    private function getDirectoryData(string $path): array
    {
        $pathArray = explode('/', $path);
        return [
            'text' => count($pathArray) > 0 ? end($pathArray) : $path,
            'id' => $path,
            'li_attr' => ['data-id' => $path],
            'path' => $path,
            'path_array' => $pathArray
        ];
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
                $found = $node['path_array'][$level] === $tnode['path_array'][$level];
                if ($found) {
                    $level ++;
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
