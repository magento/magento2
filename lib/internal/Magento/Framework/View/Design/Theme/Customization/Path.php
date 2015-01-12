<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Theme Customization Path
 */
namespace Magento\Framework\View\Design\Theme\Customization;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Theme Customization Path
 */
class Path
{
    /**
     * Customization directory name
     */
    const DIR_NAME = 'theme_customization';

    /**
     * File name
     *
     * @var string
     */
    protected $filename;

    /**
     * File system
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * Media directory read
     *
     * @var \Magento\Framework\Filesystem\Directory\Read
     */
    protected $mediaDirectoryRead;

    /**
     * Theme directory read
     *
     * @var \Magento\Framework\Filesystem\Directory\Read
     */
    protected $themeDirectoryRead;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Filesystem $filesystem
     * @param string $filename
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        $filename = \Magento\Framework\View\ConfigInterface::CONFIG_FILE_NAME
    ) {
        $this->filesystem = $filesystem;
        $this->filename = $filename;
        $this->mediaDirectoryRead = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
        $this->themeDirectoryRead = $this->filesystem->getDirectoryRead(DirectoryList::THEMES);
    }

    /**
     * Returns customization absolute path
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @return string|null
     */
    public function getCustomizationPath(\Magento\Framework\View\Design\ThemeInterface $theme)
    {
        $path = null;
        if ($theme->getId()) {
            $path = $this->mediaDirectoryRead->getAbsolutePath(self::DIR_NAME . '/' . $theme->getId());
        }
        return $path;
    }

    /**
     * Get directory where themes files are stored
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @return string|null
     */
    public function getThemeFilesPath(\Magento\Framework\View\Design\ThemeInterface $theme)
    {
        $path = null;
        if ($theme->getFullPath()) {
            $path = $this->themeDirectoryRead->getAbsolutePath($theme->getFullPath());
        }
        return $path;
    }

    /**
     * Get path to custom view configuration file
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @return string|null
     */
    public function getCustomViewConfigPath(\Magento\Framework\View\Design\ThemeInterface $theme)
    {
        $path = null;
        if ($theme->getId()) {
            $path = $this->mediaDirectoryRead->getAbsolutePath(
                self::DIR_NAME . '/' . $theme->getId() . '/' . $this->filename
            );
        }
        return $path;
    }
}
