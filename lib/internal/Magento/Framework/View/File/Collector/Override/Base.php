<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\File\Collector\Override;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\File\CollectorInterface;
use Magento\Framework\View\File\Factory as FileFactory;
use Magento\Framework\View\Helper\PathPattern;

/**
 * Source of view files that explicitly override base files introduced by modules
 * @since 2.0.0
 */
class Base implements CollectorInterface
{
    /**
     * Pattern helper
     *
     * @var PathPattern
     * @since 2.0.0
     */
    private $pathPatternHelper;

    /**
     * File factory
     *
     * @var FileFactory
     * @since 2.0.0
     */
    private $fileFactory;

    /**
     * Directory factory
     *
     * @var ReadFactory
     * @since 2.0.0
     */
    private $readDirFactory;

    /**
     * Component registrar
     *
     * @var ComponentRegistrarInterface
     * @since 2.0.0
     */
    private $componentRegistrar;

    /**
     * Sub-directory path
     *
     * @var string
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @param ThemeInterface $theme
     * @param string $filePath
     * @return \Magento\Framework\View\File[]
     * @since 2.0.0
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
        $searchPattern = "{$namespace}_{$module}/{$this->subDir}{$filePath}";
        $files = $themeDir->search($searchPattern);
        $result = [];
        $pattern = "#(?<moduleName>[^/]+)/{$this->subDir}"
            . $this->pathPatternHelper->translatePatternFromGlob($filePath) . "$#i";
        foreach ($files as $file) {
            $filename = $themeDir->getAbsolutePath($file);
            if (!preg_match($pattern, $filename, $matches)) {
                continue;
            }
            $result[] = $this->fileFactory->create($filename, $matches['moduleName']);
        }
        return $result;
    }
}
