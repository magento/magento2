<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\PageLayout\File\Collector;

use Magento\Framework\View\Design\ThemeInterface;

/**
 * Source of layout files aggregated from a theme and its parents according to merging and overriding conventions
 */
class Aggregated extends \Magento\Framework\View\Layout\File\Collector\Aggregated
{
    /**
     * @param ThemeInterface $theme
     * @param string $filePath
     * @return array
     */
    public function getFilesContent(ThemeInterface $theme, $filePath)
    {
        $result = [];
        foreach ($this->getFiles($theme, $filePath) as $file) {
            $result[$file->getFilename()] = file_get_contents($file->getFilename());
        }

        return $result;
    }
}
