<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\View\Design\Theme;

/**
 * Theme customizations manager
 */
class Customization implements CustomizationInterface
{
    /**
     * File provider
     *
     * @var \Magento\Framework\View\Design\Theme\FileProviderInterface
     */
    protected $fileProvider;

    /**
     * Theme customization path
     *
     * @var \Magento\Framework\View\Design\Theme\Customization\Path
     */
    protected $customizationPath;

    /**
     * Theme
     *
     * @var \Magento\Framework\View\Design\ThemeInterface
     */
    protected $theme;

    /**
     * Theme files
     *
     * @var \Magento\Framework\View\Design\Theme\FileInterface[]
     */
    protected $themeFiles;

    /**
     * Theme files by type
     *
     * @var \Magento\Framework\View\Design\Theme\FileInterface[]
     */
    protected $themeFilesByType = array();

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Design\Theme\FileProviderInterface $fileProvider
     * @param \Magento\Framework\View\Design\Theme\Customization\Path $customizationPath
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
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
     */
    public function getFilesByType($type)
    {
        if (!isset($this->themeFilesByType[$type])) {
            $this->themeFilesByType[$type] = $this->fileProvider->getItems($this->theme, array('file_type' => $type));
        }
        return $this->themeFilesByType[$type];
    }

    /**
     * Get short file information
     *
     * @param \Magento\Framework\View\Design\Theme\FileInterface[] $files
     * @return array
     */
    public function generateFileInfo(array $files)
    {
        $filesInfo = array();
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
     */
    public function getCustomizationPath()
    {
        return $this->customizationPath->getCustomizationPath($this->theme);
    }

    /**
     * Get directory where themes files are stored
     *
     * @return null|string
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
