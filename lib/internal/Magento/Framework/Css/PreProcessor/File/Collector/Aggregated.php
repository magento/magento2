<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Css\PreProcessor\File\Collector;

use Magento\Framework\Module\ModuleManagerInterface;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\File\CollectorInterface;
use Magento\Framework\View\File\FileList\Factory;
use Psr\Log\LoggerInterface;

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
     * @var \Magento\Framework\Module\ModuleManagerInterface
     */
    protected $moduleManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Factory $fileListFactory
     * @param CollectorInterface $libraryFiles
     * @param CollectorInterface $baseFiles
     * @param CollectorInterface $overriddenBaseFiles
     * @param ModuleManagerInterface $moduleManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        Factory $fileListFactory,
        CollectorInterface $libraryFiles,
        CollectorInterface $baseFiles,
        CollectorInterface $overriddenBaseFiles,
        ModuleManagerInterface $moduleManager,
        LoggerInterface $logger
    ) {
        $this->fileListFactory = $fileListFactory;
        $this->libraryFiles = $libraryFiles;
        $this->baseFiles = $baseFiles;
        $this->overriddenBaseFiles = $overriddenBaseFiles;
        $this->moduleManager = $moduleManager;
        $this->logger = $logger;
    }

    /**
     * Retrieve files
     *
     * Aggregate source files from modules and a theme and its ancestors
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @param string $filePath
     * @return \Magento\Framework\View\File[]
     * @throws \LogicException
     */
    public function getFiles(ThemeInterface $theme, $filePath)
    {
        $list = $this->fileListFactory->create(\Magento\Framework\Css\PreProcessor\File\FileList\Collator::class);
        $list->add($this->libraryFiles->getFiles($theme, $filePath));
        $list->add($this->baseFiles->getFiles($theme, $filePath));

        foreach ($theme->getInheritedThemes() as $currentTheme) {
            $files = $this->overriddenBaseFiles->getFiles($currentTheme, $filePath);
            $list->replace($files);
        }
        $result = $list->getAll();

        $finalResult = [];

        if(!empty($result))
        {
            foreach ($result as $file)
            {
                if ($this->hasEnabledModule($file)) {
                    $finalResult[] = $file;
                }
            }
        }

        if (empty($finalResult)) {
            $this->logger->notice(
                'magento_import returns empty result by path ' . $filePath . ' for theme ' . $theme->getCode()
            );
        }


        return $finalResult;
    }

    private function hasEnabledModule(\Magento\Framework\View\File $file)
    {
        return $file instanceof \Magento\Framework\View\File && (!$file->getModule() ||
                $this->moduleManager->isEnabled($file->getModule()));
    }
}
