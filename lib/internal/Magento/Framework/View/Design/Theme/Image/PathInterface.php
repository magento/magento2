<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme\Image;

use Magento\Framework\View\Design\ThemeInterface;

/**
 * Theme Image Path interface
 */
interface PathInterface
{
    /**
     * Image preview path
     */
    const PREVIEW_DIRECTORY_PATH = 'theme/preview';

    /**
     * Get preview image directory url
     *
     * @param ThemeInterface $theme
     * @return string
     */
    public function getPreviewImageUrl(ThemeInterface $theme);

    /**
     * Get path to preview image
     *
     * @param ThemeInterface $theme
     * @return string
     */
    public function getPreviewImagePath(ThemeInterface $theme);

    /**
     * Return default themes preview image url
     *
     * @return string
     */
    public function getPreviewImageDefaultUrl();

    /**
     * Get directory path for preview image
     *
     * @return string
     */
    public function getImagePreviewDirectory();

    /**
     * Temporary directory path to store images
     *
     * @return string
     */
    public function getTemporaryDirectory();
}
