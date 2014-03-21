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
namespace Magento\View\Layout\File\Source;

use Magento\View\Layout\File\SourceInterface;
use Magento\View\Design\ThemeInterface;
use Magento\View\Layout\File\FileList\Factory;

/**
 * Source of layout files aggregated from a theme and its parents according to merging and overriding conventions
 */
class Aggregated implements SourceInterface
{
    /**
     * File list factory
     *
     * @var Factory
     */
    protected $fileListFactory;

    /**
     * Base files
     *
     * @var SourceInterface
     */
    protected $baseFiles;

    /**
     * Theme files
     *
     * @var SourceInterface
     */
    protected $themeFiles;

    /**
     * Overridden base files
     *
     * @var SourceInterface
     */
    protected $overrideBaseFiles;

    /**
     * Overridden theme files
     *
     * @var SourceInterface
     */
    protected $overrideThemeFiles;

    /**
     * Constructor
     *
     * @param Factory $fileListFactory
     * @param SourceInterface $baseFiles
     * @param SourceInterface $themeFiles
     * @param SourceInterface $overrideBaseFiles
     * @param SourceInterface $overrideThemeFiles
     */
    public function __construct(
        Factory $fileListFactory,
        SourceInterface $baseFiles,
        SourceInterface $themeFiles,
        SourceInterface $overrideBaseFiles,
        SourceInterface $overrideThemeFiles
    ) {
        $this->fileListFactory = $fileListFactory;
        $this->baseFiles = $baseFiles;
        $this->themeFiles = $themeFiles;
        $this->overrideBaseFiles = $overrideBaseFiles;
        $this->overrideThemeFiles = $overrideThemeFiles;
    }

    /**
     * Retrieve files
     *
     * Aggregate layout files from modules and a theme and its ancestors
     *
     * @param ThemeInterface $theme
     * @param string $filePath
     * @return \Magento\View\Layout\File[]
     */
    public function getFiles(ThemeInterface $theme, $filePath = '*')
    {
        $list = $this->fileListFactory->create();
        $list->add($this->baseFiles->getFiles($theme, $filePath));

        foreach ($theme->getInheritedThemes() as $currentTheme) {
            $list->add($this->themeFiles->getFiles($currentTheme, $filePath));
            $list->replace($this->overrideBaseFiles->getFiles($currentTheme, $filePath));
            $list->replace($this->overrideThemeFiles->getFiles($currentTheme, $filePath));
        }
        return $list->getAll();
    }
}
