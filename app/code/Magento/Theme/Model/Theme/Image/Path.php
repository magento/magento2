<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Model\Theme\Image;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface as DirectoryReadInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\Design\Theme\Image\PathInterface as ImagePathInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Theme Image Path
 */
class Path implements ImagePathInterface
{
    /**
     * Default theme preview image
     */
    public const DEFAULT_PREVIEW_IMAGE = 'Magento_Theme::theme/default_preview.jpg';

    /**
     * @var DirectoryReadInterface
     */
    protected $mediaDirectory;

    /**
     * Initialize dependencies
     *
     * @param Filesystem $filesystem
     * @param AssetRepository $assetRepo
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Filesystem $filesystem,
        protected readonly AssetRepository $assetRepo,
        protected readonly StoreManagerInterface $storeManager
    ) {
        $this->mediaDirectory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
    }

    /**
     * Get url to preview image
     *
     * @param ThemeInterface $theme
     * @return string
     */
    public function getPreviewImageUrl(ThemeInterface $theme)
    {
        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
                . self::PREVIEW_DIRECTORY_PATH . '/' . $theme->getPreviewImage();
    }

    /**
     * Get path to preview image
     *
     * @param ThemeInterface $theme
     * @return string
     */
    public function getPreviewImagePath(ThemeInterface $theme)
    {
        return $this->mediaDirectory->getAbsolutePath(self::PREVIEW_DIRECTORY_PATH . '/' . $theme->getPreviewImage());
    }

    /**
     * Return default themes preview image url
     *
     * @return string
     */
    public function getPreviewImageDefaultUrl()
    {
        return $this->assetRepo->getUrl(self::DEFAULT_PREVIEW_IMAGE);
    }

    /**
     * Get directory path for preview image
     *
     * @return string
     */
    public function getImagePreviewDirectory()
    {
        return $this->mediaDirectory->getAbsolutePath(self::PREVIEW_DIRECTORY_PATH);
    }

    /**
     * Temporary directory path to store images
     *
     * @return string
     */
    public function getTemporaryDirectory()
    {
        return $this->mediaDirectory->getAbsolutePath('theme/origin');
    }
}
