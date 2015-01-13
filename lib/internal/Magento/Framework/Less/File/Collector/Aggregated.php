<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Less\File\Collector;

use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\File\CollectorInterface;
use Magento\Framework\View\File\FileList\Factory;

/**
 * Source of layout files aggregated from a theme and its parents according to merging and overriding conventions
 */
class Aggregated implements CollectorInterface
{
    /**
     * @var Factory
     */
    protected $fileListFactory;

    /**
     * @var \Magento\Framework\View\File\CollectorInterface
     */
    protected $libraryFiles;

    /**
     * @var \Magento\Framework\View\File\CollectorInterface
     */
    protected $baseFiles;

    /**
     * @var \Magento\Framework\View\File\CollectorInterface
     */
    protected $overriddenBaseFiles;

    /**
     * @param Factory $fileListFactory
     * @param CollectorInterface $libraryFiles
     * @param CollectorInterface $baseFiles
     * @param CollectorInterface $overriddenBaseFiles
     */
    public function __construct(
        Factory $fileListFactory,
        CollectorInterface $libraryFiles,
        CollectorInterface $baseFiles,
        CollectorInterface $overriddenBaseFiles
    ) {
        $this->fileListFactory = $fileListFactory;
        $this->libraryFiles = $libraryFiles;
        $this->baseFiles = $baseFiles;
        $this->overriddenBaseFiles = $overriddenBaseFiles;
    }

    /**
     * Retrieve files
     *
     * Aggregate LESS files from modules and a theme and its ancestors
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @param string $filePath
     * @return \Magento\Framework\View\File[]
     * @throws \LogicException
     */
    public function getFiles(ThemeInterface $theme, $filePath)
    {
        $list = $this->fileListFactory->create('Magento\Framework\Less\File\FileList\Collator');
        $list->add($this->libraryFiles->getFiles($theme, $filePath));
        $list->add($this->baseFiles->getFiles($theme, $filePath));

        foreach ($theme->getInheritedThemes() as $currentTheme) {
            $files = $this->overriddenBaseFiles->getFiles($currentTheme, $filePath);
            $list->replace($files);
        }
        $result = $list->getAll();
        if (empty($result)) {
            throw new \LogicException('magento_import returns empty result by path ' . $filePath);
        }
        return $result;
    }
}
