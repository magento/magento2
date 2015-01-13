<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\File;

use Magento\Framework\View\Design\ThemeInterface;

/**
 * Interface of locating view files in the file system
 */
interface CollectorInterface
{
    /**
     * Retrieve instances of view files
     *
     * @param ThemeInterface $theme Theme that defines the design context
     * @param string $filePath
     * @return \Magento\Framework\View\File[]
     */
    public function getFiles(ThemeInterface $theme, $filePath);
}
