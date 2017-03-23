<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\File\Collector;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\File\CollectorInterface;
use Magento\Framework\View\File\Factory as FileFactory;
use Magento\Framework\View\Helper\PathPattern;

/**
 * Source of modular view files introduced by a theme
 */
class ThemeModular implements CollectorInterface
{
    /**
     * @var PathPattern
     */
    private $pathPatternHelper;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var ReadFactory
     */
    private $readDirFactory;

    /**
     * @var ComponentRegistrarInterface
     */
    private $componentRegistrar;

    /**
     * @var string
     */
    private $subDir;

    /**
     * Constructor
     *
     * @param FileFactory $fileFactory
     * @param ReadFactory $readDirFactory
     * @param ComponentRegistrarInterface $componentRegistrar
     * @param PathPattern $pathPatternHelper
     * @param string $subDir
     */
    public function __construct(
        FileFactory $fileFactory,
        ReadFactory $readDirFactory,
        ComponentRegistrarInterface $componentRegistrar,
        PathPattern $pathPatternHelper,
        $subDir = ''
    ) {
        $this->pathPatternHelper = $pathPatternHelper;
        $this->fileFactory = $fileFactory;
        $this->readDirFactory = $readDirFactory;
        $this->componentRegistrar = $componentRegistrar;
        $this->subDir = $subDir ? $subDir . '/' : '';
    }

    /**
     * Retrieve files
     *
     * @param \Magento\Framework\View\Design\ThemeInterface $theme
     * @param string $filePath
     * @return \Magento\Framework\View\File[]
     */
    public function getFiles(ThemeInterface $theme, $filePath)
    {
        $namespace = $module = '*';
        $themePath = $theme->getFullPath();
        if (empty($themePath)) {
            return [];
        }
        $themeAbsolutePath = $this->componentRegistrar->getPath(ComponentRegistrar::THEME, $themePath);
        if (!$themeAbsolutePath) {
            return [];
        }
        $themeDir = $this->readDirFactory->create($themeAbsolutePath);
        $files = $themeDir->search("{$namespace}_{$module}/{$this->subDir}$filePath");
        $result = [];
        $pattern = "#/(?<moduleName>[^/]+)/{$this->subDir}"
            . $this->pathPatternHelper->translatePatternFromGlob($filePath) . "$#i";
        foreach ($files as $file) {
            $filename = $themeDir->getAbsolutePath($file);
            if (!preg_match($pattern, $filename, $matches)) {
                continue;
            }
            $result[] = $this->fileFactory->create($filename, $matches['moduleName'], $theme);
        }
        return $result;
    }
}
