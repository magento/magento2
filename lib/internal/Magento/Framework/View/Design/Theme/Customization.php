<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Design\Theme;

/**
 * Theme customizations manager
 * @since 2.0.0
 */
class Customization implements CustomizationInterface
{
    /**
     * File provider
     *
     * @var \Magento\Framework\View\Design\Theme\FileProviderInterface
     * @since 2.0.0
     */
    protected $fileProvider;

    /**
     * Theme customization path
     *
     * @var \Magento\Framework\View\Design\Theme\Customization\Path
     * @since 2.0.0
     */
    protected $customizationPath;

    /**
     * Theme
     *
     * @var \Magento\Framework\View\Design\ThemeInterface
     * @since 2.0.0
     */
    protected $theme;

    /**
     * Theme files
     *
     * @var \Magento\Framework\View\Design\Theme\FileInterface[]
     * @since 2.0.0
     */
    protected $themeFiles;

    /**
     * Theme files by type
     *
     * @var \Magento\Framework\View\Design\Theme\FileInterface[]
     * @since 2.0.0
     */
    protected $themeFilesByType = [];

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Design\Theme\FileProviderInterface $fileProvider
     * @param \Magento\Framework\View\Design\Theme\Customization\Path $customizationPath
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Design\Theme\FileProviderInterface $fileProvider,
        \Magento\Framework\View\Design\Theme\Customization\Path $customizationPath,
        \Magento\Framework\View\Design\ThemeInterface $theme = null
    ) {
        $this->fileProvider = $fileProvider;
        $this->customizationPath = $customizationPath;
        $this->theme = $theme;
    }

    /**
     * Retrieve list of files which belong to a theme
     *
     * @return \Magento\Framework\View\Design\Theme\FileInterface[]
     * @since 2.0.0
     */
    public function getFiles()
    {
        if (!$this->themeFiles) {
            $this->themeFiles = $this->fileProvider->getItems($this->theme);
        }
        return $this->themeFiles;
    }

    /**
     * Retrieve list of files which belong to a theme only by type
     *
     * @param string $type
     * @return \Magento\Framework\View\Design\Theme\FileInterface[]
     * @since 2.0.0
     */
    public function getFilesByType($type)
    {
        if (!isset($this->themeFilesByType[$type])) {
            $this->themeFilesByType[$type] = $this->fileProvider->getItems($this->theme, ['file_type' => $type]);
        }
        return $this->themeFilesByType[$type];
    }

    /**
     * Get short file information
     *
     * @param \Magento\Framework\View\Design\Theme\FileInterface[] $files
     * @return array
     * @since 2.0.0
     */
    public function generateFileInfo(array $files)
    {
        $filesInfo = [];
        /** @var $file \Magento\Framework\View\Design\Theme\FileInterface */
        foreach ($files as $file) {
            if ($file instanceof \Magento\Framework\View\Design\Theme\FileInterface) {
                $filesInfo[] = $file->getFileInfo();
            }
        }
        return $filesInfo;
    }

    /**
     * Returns customization absolute path
     *
     * @return null|string
     * @since 2.0.0
     */
    public function getCustomizationPath()
    {
        return $this->customizationPath->getCustomizationPath($this->theme);
    }

    /**
     * Get directory where themes files are stored
     *
     * @return null|string
     * @since 2.0.0
     */
    public function getThemeFilesPath()
    {
        return $this->theme->isPhysical() ? $this->customizationPath->getThemeFilesPath(
            $this->theme
        ) : $this->customizationPath->getCustomizationPath(
            $this->theme
        );
    }

    /**
     * Get path to custom view configuration file
     *
     * @return null|string
     * @since 2.0.0
     */
    public function getCustomViewConfigPath()
    {
        return $this->customizationPath->getCustomViewConfigPath($this->theme);
    }

    /**
     * Reorder
     *
     * @param string $type
     * @param array $sequence
     * @return $this|CustomizationInterface
     * @since 2.0.0
     */
    public function reorder($type, array $sequence)
    {
        $sortOrderSequence = array_flip(array_values($sequence));
        /** @var $file \Magento\Framework\View\Design\Theme\FileInterface */
        foreach ($this->getFilesByType($type) as $file) {
            if (isset($sortOrderSequence[$file->getId()])) {
                $prevSortOrder = $file->getData('sort_order');
                $currentSortOrder = $sortOrderSequence[$file->getId()];
                if ($prevSortOrder !== $currentSortOrder) {
                    $file->setData('sort_order', $currentSortOrder);
                    $file->save();
                }
            }
        }
        return $this;
    }

    /**
     * Remove custom files by ids
     *
     * @param array $fileIds
     * @return $this
     * @since 2.0.0
     */
    public function delete(array $fileIds)
    {
        /** @var $file \Magento\Framework\View\Design\Theme\FileInterface */
        foreach ($this->getFiles() as $file) {
            if (in_array($file->getId(), $fileIds)) {
                $file->delete();
            }
        }
        return $this;
    }
}
