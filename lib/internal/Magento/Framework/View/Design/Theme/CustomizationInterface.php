<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme;

/**
 * Theme customization interface
 */
interface CustomizationInterface
{
    /**
     * Retrieve list of files which belong to a theme
     *
     * @return \Magento\Framework\View\Design\Theme\Customization\FileInterface[]
     */
    public function getFiles();

    /**
     * Retrieve list of files which belong to a theme only by type
     *
     * @param string $type
     * @return \Magento\Framework\View\Design\Theme\Customization\FileInterface[]
     */
    public function getFilesByType($type);

    /**
     * Returns customization absolute path
     *
     * @return string
     */
    public function getCustomizationPath();

    /**
     * Get directory where themes files are stored
     *
     * @return string
     */
    public function getThemeFilesPath();

    /**
     * Get path to custom view configuration file
     *
     * @return string
     */
    public function getCustomViewConfigPath();

    /**
     * Reorder files positions
     *
     * @param string $type
     * @param array $sequence
     * @return CustomizationInterface
     */
    public function reorder($type, array $sequence);

    /**
     * Remove custom files by ids
     *
     * @param array $fileIds
     * @return $this
     */
    public function delete(array $fileIds);
}
