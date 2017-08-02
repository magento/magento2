<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Model\Theme\Image;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Design\ThemeInterface;

/**
 * Theme Image Path
 * @since 2.0.0
 */
class Path implements \Magento\Framework\View\Design\Theme\Image\PathInterface
{
    /**
     * Default theme preview image
     */
    const DEFAULT_PREVIEW_IMAGE = 'Magento_Theme::theme/default_preview.jpg';

    /**
     * Media Directory
     *
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     * @since 2.0.0
     */
    protected $mediaDirectory;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     * @since 2.0.0
     */
    protected $assetRepo;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $storeManager;

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->mediaDirectory = $filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $this->assetRepo = $assetRepo;
        $this->storeManager = $storeManager;
    }

    /**
     * Get url to preview image
     *
     * @param ThemeInterface $theme
     * @return string
     * @since 2.0.0
     */
    public function getPreviewImageUrl(ThemeInterface $theme)
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA)
                . self::PREVIEW_DIRECTORY_PATH . '/' . $theme->getPreviewImage();
    }

    /**
     * Get path to preview image
     *
     * @param ThemeInterface $theme
     * @return string
     * @since 2.0.0
     */
    public function getPreviewImagePath(ThemeInterface $theme)
    {
        return $this->mediaDirectory->getAbsolutePath(self::PREVIEW_DIRECTORY_PATH . '/' . $theme->getPreviewImage());
    }

    /**
     * Return default themes preview image url
     *
     * @return string
     * @since 2.0.0
     */
    public function getPreviewImageDefaultUrl()
    {
        return $this->assetRepo->getUrl(self::DEFAULT_PREVIEW_IMAGE);
    }

    /**
     * Get directory path for preview image
     *
     * @return string
     * @since 2.0.0
     */
    public function getImagePreviewDirectory()
    {
        return $this->mediaDirectory->getAbsolutePath(self::PREVIEW_DIRECTORY_PATH);
    }

    /**
     * Temporary directory path to store images
     *
     * @return string
     * @since 2.0.0
     */
    public function getTemporaryDirectory()
    {
        return $this->mediaDirectory->getRelativePath('/theme/origin');
    }
}
