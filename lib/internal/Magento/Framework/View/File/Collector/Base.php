<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\File\Collector;

use Magento\Framework\Filesystem;
use Magento\Framework\Module\Dir\Search;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\View\File\AbstractCollector;
use Magento\Framework\View\File\Factory as FileFactory;
use Magento\Framework\View\Helper\PathPattern as PathPatternHelper;

/**
 * Source of base files introduced by modules
 */
class Base extends AbstractCollector
{
    /**
     * @var Search
     */
    protected $dirSearch;

    /**
     * Constructor
     *
     * @param Search $dirSearch
     * @param Filesystem $filesystem
     * @param FileFactory $fileFactory
     * @param PathPatternHelper $pathPatternHelper
     * @param string $subDir
     */
    public function __construct(
        Search $dirSearch,
        Filesystem $filesystem,
        FileFactory $fileFactory,
        PathPatternHelper $pathPatternHelper,
        $subDir = ''
    ) {
        $this->dirSearch = $dirSearch;
        parent::__construct($filesystem, $fileFactory, $pathPatternHelper, $subDir);
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
        $result = [];
        $sharedFiles = $this->dirSearch->collectFiles("view/base/{$this->subDir}{$filePath}");

        $filePathPtn = $this->pathPatternHelper->translatePatternFromGlob($filePath);
        $pattern = "#(?<namespace>[^/]+)/(?<module>[^/]+)/view/base/{$this->subDir}" . $filePathPtn . "$#i";
        foreach ($sharedFiles as $file) {
            $filename = $this->directory->getAbsolutePath($file);
            if (!preg_match($pattern, $filename, $matches)) {
                continue;
            }
            $moduleFull = "{$matches['namespace']}_{$matches['module']}";
            $result[] = $this->fileFactory->create($filename, $moduleFull, null, true);
        }
        $area = $theme->getData('area');
        $themeFiles = $this->dirSearch->collectFiles("view/{$area}/{$this->subDir}{$filePath}");
        $pattern = "#(?<namespace>[^/]+)/(?<module>[^/]+)/view/{$area}/{$this->subDir}" . $filePathPtn . "$#i";
        foreach ($themeFiles as $file) {
            $filename = $this->directory->getAbsolutePath($file);
            if (!preg_match($pattern, $filename, $matches)) {
                continue;
            }
            $moduleFull = "{$matches['namespace']}_{$matches['module']}";
            $result[] = $this->fileFactory->create($filename, $moduleFull);
        }
        return $result;
    }
}
