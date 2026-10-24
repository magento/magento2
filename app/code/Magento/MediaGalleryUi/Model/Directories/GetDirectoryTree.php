<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Model\Directories;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
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
     * Return the directory folder structure in an array
     *
     * @param string|null $path
     * @param bool $loadWholeTree
     * @return array
     * @throws ValidatorException
     */
    public function execute(?string $path = null, bool $loadWholeTree = true): array
    {
        if ($loadWholeTree) {
            return $this->getDirectories();
        }

        return $this->getDirectoryNodesForPath($path);
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

        /** @var ReadInterface $mediaDirectory */
        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);

        if ($mediaDirectory->isDirectory()) {
            foreach ($this->getAllowedDirectoryPaths($mediaDirectory) as $imageFolderPath) {
                $directories[] = $this->buildDirectoryNode($mediaDirectory, $imageFolderPath);
            }
        }

        return $directories;
    }

    /**
     * Return directory nodes for a specific path (or roots when path is empty).
     *
     * @param string|null $path
     * @return array
     * @throws ValidatorException
     */
    private function getDirectoryNodesForPath(?string $path): array
    {
        $nodes = [];

        /** @var ReadInterface $mediaDirectory */
        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        if (!$mediaDirectory->isDirectory()) {
            return $nodes;
        }

        if ($path === null || $path === '') {
            foreach ($this->getAllowedDirectoryPaths($mediaDirectory) as $rootPath) {
                $nodes[] = $this->getDirectoryDataForLazyLoad($mediaDirectory, $rootPath);
            }

            return $nodes;
        }

        $normalizedPath = trim($path, '/');
        if (!$this->isPathWithinAllowedRoots($normalizedPath, $mediaDirectory)) {
            return $nodes;
        }

        if (!$mediaDirectory->isDirectory($normalizedPath)) {
            return $nodes;
        }

        foreach ($this->getSubdirectoryPaths($mediaDirectory, $normalizedPath) as $subdirectoryPath) {
            $nodes[] = $this->getDirectoryDataForLazyLoad($mediaDirectory, $subdirectoryPath);
        }

        return $nodes;
    }
    /**
     * Check whether a path is inside the configured allowed roots.
     *
     * @param string $path
     * @param ReadInterface $mediaDirectory
     * @return bool
     */
    private function isPathWithinAllowedRoots(string $path, ReadInterface $mediaDirectory): bool
    {
        foreach ($this->getAllowedDirectoryPaths($mediaDirectory) as $allowedRoot) {
            if ($path === $allowedRoot || strpos($path, $allowedRoot . '/') === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return sorted and existing top-level media gallery paths.
     *
     * @param ReadInterface $mediaDirectory
     * @return string[]
     */
    private function getAllowedDirectoryPaths(ReadInterface $mediaDirectory): array
    {
        $imageFolderPaths = $this->coreConfig->getValue(
            self::XML_PATH_MEDIA_GALLERY_IMAGE_FOLDERS,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT
        );

        if (!is_array($imageFolderPaths)) {
            return [];
        }

        sort($imageFolderPaths);

        $allowedDirectoryPaths = [];
        foreach ($imageFolderPaths as $imageFolderPath) {
            if (is_string($imageFolderPath)
                && $imageFolderPath !== ''
                && !$this->isPathExcluded->execute($imageFolderPath)
                && $mediaDirectory->isDirectory($imageFolderPath)
            ) {
                $allowedDirectoryPaths[] = $imageFolderPath;
            }
        }

        return $allowedDirectoryPaths;
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
     * Build directory tree recursively for a root folder.
     *
     * @param ReadInterface $mediaDirectory
     * @param string $path
     * @return array
     */
    private function buildDirectoryNode(ReadInterface $mediaDirectory, string $path): array
    {
        $node = $this->getDirectoryData($path);
        $node['children'] = [];

        foreach ($this->getSubdirectoryPaths($mediaDirectory, $path) as $subdirectoryPath) {
            $node['children'][] = $this->buildDirectoryNode($mediaDirectory, $subdirectoryPath);
        }

        return $node;
    }

    /**
     * Build jstree node for on-demand loading.
     *
     * @param ReadInterface $mediaDirectory
     * @param string $path
     * @return array
     */
    private function getDirectoryDataForLazyLoad(ReadInterface $mediaDirectory, string $path): array
    {
        $node = $this->getDirectoryData($path);
        $node['children'] = $this->hasSubdirectories($mediaDirectory, $path);

        return $node;
    }

    /**
     * Return sorted subdirectories for a given folder.
     *
     * @param ReadInterface $mediaDirectory
     * @param string $path
     * @return string[]
     * @throws ValidatorException
     */
    private function getSubdirectoryPaths(ReadInterface $mediaDirectory, string $path): array
    {
        $subdirectories = [];

        foreach ($mediaDirectory->read($path) as $entryPath) {
            if ($mediaDirectory->isDirectory($entryPath) && !$this->isPathExcluded->execute($entryPath)) {
                $subdirectories[] = $entryPath;
            }
        }

        sort($subdirectories);

        return $subdirectories;
    }

    /**
     * Check whether path has at least one visible subdirectory.
     *
     * @param ReadInterface $mediaDirectory
     * @param string $path
     * @return bool
     * @throws ValidatorException
     */
    private function hasSubdirectories(ReadInterface $mediaDirectory, string $path): bool
    {
        foreach ($mediaDirectory->read($path) as $entryPath) {
            if ($mediaDirectory->isDirectory($entryPath) && !$this->isPathExcluded->execute($entryPath)) {
                return true;
            }
        }

        return false;
    }
}
