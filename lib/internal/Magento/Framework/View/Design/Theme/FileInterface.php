<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme;

/**
 * Theme file interface
 * @since 2.0.0
 */
interface FileInterface
{
    /**
     * Set customization service model
     *
     * @param Customization\FileInterface $service
     * @return $this
     * @since 2.0.0
     */
    public function setCustomizationService(Customization\FileInterface $service);

    /**
     * Get customization service model
     *
     * @return \Magento\Framework\View\Design\Theme\Customization\FileInterface
     * @since 2.0.0
     */
    public function getCustomizationService();

    /**
     * Attaches selected theme to current file
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @return $this
     * @since 2.0.0
     */
    public function setTheme(\Magento\Framework\View\Design\ThemeInterface $theme);

    /**
     * Get theme model
     *
     * @return \Magento\Framework\View\Design\ThemeInterface
     * @since 2.0.0
     */
    public function getTheme();

    /**
     * Set filename of custom file
     *
     * @param string $fileName
     * @return $this
     * @since 2.0.0
     */
    public function setFileName($fileName);

    /**
     * Get filename of custom file
     *
     * @return string|null
     * @since 2.0.0
     */
    public function getFileName();

    /**
     * Return absolute path to file of customization
     *
     * @return string
     * @since 2.0.0
     */
    public function getFullPath();

    /**
     * Get short file information which can be serialized
     *
     * @return array
     * @since 2.0.0
     */
    public function getFileInfo();

    /**
     * Get content of current file
     *
     * @return string
     * @since 2.0.0
     */
    public function getContent();

    /**
     * Save custom file
     *
     * @return $this
     * @since 2.0.0
     */
    public function save();

    /**
     * Delete custom file
     *
     * @return $this
     * @since 2.0.0
     */
    public function delete();
}
