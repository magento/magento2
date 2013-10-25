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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Source of layout files aggregated from a theme and its parents according to merging and overriding conventions
 */
namespace Magento\Core\Model\Layout\File\Source;

class Aggregated implements \Magento\Core\Model\Layout\File\SourceInterface
{
    /**
     * @var \Magento\Core\Model\Layout\File\FileList\Factory
     */
    private $_fileListFactory;

    /**
     * @var \Magento\Core\Model\Layout\File\SourceInterface
     */
    private $_baseFiles;

    /**
     * @var \Magento\Core\Model\Layout\File\SourceInterface
     */
    private $_themeFiles;

    /**
     * @var \Magento\Core\Model\Layout\File\SourceInterface
     */
    private $_overridingBaseFiles;

    /**
     * @var \Magento\Core\Model\Layout\File\SourceInterface
     */
    private $_overridingThemeFiles;

    /**
     * @param \Magento\Core\Model\Layout\File\FileList\Factory $fileListFactory
     * @param \Magento\Core\Model\Layout\File\SourceInterface $baseFiles
     * @param \Magento\Core\Model\Layout\File\SourceInterface $themeFiles
     * @param \Magento\Core\Model\Layout\File\SourceInterface $overridingBaseFiles
     * @param \Magento\Core\Model\Layout\File\SourceInterface $overridingThemeFiles
     */
    public function __construct(
        \Magento\Core\Model\Layout\File\FileList\Factory $fileListFactory,
        \Magento\Core\Model\Layout\File\SourceInterface $baseFiles,
        \Magento\Core\Model\Layout\File\SourceInterface $themeFiles,
        \Magento\Core\Model\Layout\File\SourceInterface $overridingBaseFiles,
        \Magento\Core\Model\Layout\File\SourceInterface $overridingThemeFiles
    ) {
        $this->_fileListFactory = $fileListFactory;
        $this->_baseFiles = $baseFiles;
        $this->_themeFiles = $themeFiles;
        $this->_overridingBaseFiles = $overridingBaseFiles;
        $this->_overridingThemeFiles = $overridingThemeFiles;
    }

    /**
     * Aggregate layout files from modules and a theme and its ancestors
     *
     * {@inheritdoc}
     */
    public function getFiles(\Magento\View\Design\ThemeInterface $theme)
    {
        $list = $this->_fileListFactory->create();
        $list->add($this->_baseFiles->getFiles($theme));
        foreach ($this->_getInheritedThemes($theme) as $currentTheme) {
            $list->add($this->_themeFiles->getFiles($currentTheme));
            $list->replace($this->_overridingBaseFiles->getFiles($currentTheme));
            $list->replace($this->_overridingThemeFiles->getFiles($currentTheme));
        }
        return $list->getAll();
    }

    /**
     * Return the full theme inheritance sequence, from the root theme till a specified one
     *
     * @param \Magento\View\Design\ThemeInterface $theme
     * @return \Magento\View\Design\ThemeInterface[] Format: array([<root_theme>, ..., <parent_theme>,] <current_theme>)
     */
    protected function _getInheritedThemes(\Magento\View\Design\ThemeInterface $theme)
    {
        $result = array();
        while ($theme) {
            $result[] = $theme;
            $theme = $theme->getParentTheme();
        }
        return array_reverse($result);
    }
}
