<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\File\Collector;

use Magento\Framework\View\File\AbstractCollector;
use Magento\Framework\View\Design\ThemeInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Source of view files introduced by a theme
 */
class Theme extends AbstractCollector
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
        $themePath = $theme->getFullPath();
        $files = $this->directory->search("{$themePath}/{$this->subDir}{$filePath}");
        $result = [];
        foreach ($files as $file) {
            $filename = $this->directory->getAbsolutePath($file);
            $result[] = $this->fileFactory->create($filename, null, $theme);
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
