<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\Directory;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\File\WriteInterface;
use Magento\MediaGalleryApi\Api\IsPathExcludedInterface;
use Magento\MediaGalleryApi\Model\ExcludedPatternsConfigInterface;

/**
 * Check if the path is excluded for media gallery. Directory path may be blacklisted if it's reserved by the system
 */
class IsExcluded implements IsPathExcludedInterface
{
    private const MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH
        = 'system/media_storage_configuration/allowed_resources/media_gallery_image_folders';

    /**
     * @var ExcludedPatternsConfigInterface
     * @deprecated
     */
    private $config;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /** @var WriteInterface */
    private $mediaDirectory;

    /**
     * @var ScopeConfigInterface
     */
    private $coreConfig;

    /**
     * @var string
     */
    private $allowedPathPattern;

    /**
     * @param ExcludedPatternsConfigInterface $config
     * @param Filesystem $filesystem
     * @param ScopeConfigInterface $coreConfig
     */
    public function __construct(
        ExcludedPatternsConfigInterface $config,
        Filesystem $filesystem,
        ScopeConfigInterface $coreConfig = null
    ) {
        $this->config = $config;
        $this->filesystem = $filesystem;
        $this->mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->coreConfig = $coreConfig ?: ObjectManager::getInstance()->get(ScopeConfigInterface::class);
    }

    /**
     * Check if the directory path can be used in the media gallery operations
     *
     * @param string $path
     * @return bool
     */
    public function execute(string $path): bool
    {
        $realPath = $this->mediaDirectory->getDriver()->getRealPathSafety($path);
        return preg_match($this->getAllowedPathPattern(), $realPath) != 1;
    }

    /**
     * Get allowed path pattern
     *
     * @return string
     */
    private function getAllowedPathPattern()
    {
        if (null === $this->allowedPathPattern) {
            $mediaGalleryImageFolders = $this->coreConfig->getValue(
                self::MEDIA_GALLERY_IMAGE_FOLDERS_CONFIG_PATH,
                'default'
            );
            $regExp = '/^(';
            $or = '';
            foreach ($mediaGalleryImageFolders as $folder) {
                $folderPattern = $folder !== null ? str_replace('/', '[\/]+', $folder) : '';
                $regExp .= $or . $folderPattern . '\b(?!-)(?:\/?[^\/]+)*\/?$';
                $or = '|';
            }
            $regExp .= ')/';
            $this->allowedPathPattern = $regExp;
        }
        return $this->allowedPathPattern;
    }
}
