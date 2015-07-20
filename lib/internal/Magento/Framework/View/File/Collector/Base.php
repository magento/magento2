<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\File\Collector;

use Magento\Framework\View\File\AbstractCollector;
use Magento\Framework\View\Design\ThemeInterface;

/**
 * Source of base files introduced by modules
 */
class Base extends AbstractCollector
{
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
        $namespace = $module = '*';
        $sharedFiles = $this->directory->search("{$namespace}/{$module}/view/base/{$this->subDir}{$filePath}");

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
        $themeFiles = $this->directory->search("{$namespace}/{$module}/view/{$area}/{$this->subDir}{$filePath}");
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
