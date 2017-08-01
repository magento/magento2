<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\File;

use Magento\Framework\View\Design\ThemeInterface;

/**
 * Interface of locating view files in the file system
 *
 * @api
 * @since 2.0.0
 */
interface CollectorInterface
{
    /**
     * Retrieve instances of view files
     *
     * File path supports the following glob patterns which are translated into regular expressions:
     *   1. ? -> [^\]
     *   2. * -> [^\]*
     *   3. [...], [!...] -> [...], [^...]
     *   4. {..,..,...} -> (?:..|..|...)
     *
     * @param ThemeInterface $theme Theme that defines the design context
     * @param string $filePath
     * @return \Magento\Framework\View\File[]
     * @since 2.0.0
     */
    public function getFiles(ThemeInterface $theme, $filePath);
}
