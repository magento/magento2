<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\File\Collector\Override;

use Magento\Framework\View\File\AbstractCollector;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Source of view files that explicitly override base files introduced by modules
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
        $namespace = $module = '*';
        $themePath = $theme->getFullPath();
        $searchPattern = "{$themePath}/{$namespace}_{$module}/{$this->subDir}{$filePath}";
        $files = $this->directory->search($searchPattern);
        $result = [];
        $pattern = "#(?<moduleName>[^/]+)/{$this->subDir}"
            . $this->pathPatternHelper->translatePatternFromGlob($filePath) . "$#i";
        foreach ($files as $file) {
            $filename = $this->directory->getAbsolutePath($file);
            if (!preg_match($pattern, $filename, $matches)) {
                continue;
            }
            $result[] = $this->fileFactory->create($filename, $matches['moduleName']);
        }
        return $result;
    }

    /**
     * Get scope directory of this file collector
     *
     * @return string
     */
    protected function getScopeDirectory()
    {
        return DirectoryList::THEMES;
    }
}
