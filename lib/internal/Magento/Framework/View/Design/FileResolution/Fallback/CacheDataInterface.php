<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Design\FileResolution\Fallback;

interface CacheDataInterface
{
    /**
     * Retrieve cached value by file name and parameters
     *
     * @param string $type
     * @param string $file
     * @param string $area
     * @param string $themePath
     * @param string $locale
     * @param string $module
     * @return string
     */
    public function getFromCache($type, $file, $area, $themePath, $locale, $module);

    /**
     * Save value to cache as unique to file name and parameters
     *
     * @param string $value
     * @param string $type
     * @param string $file
     * @param string $area
     * @param string $themePath
     * @param string $locale
     * @param string $module
     * @return bool
     */
    public function saveToCache($value, $type, $file, $area, $themePath, $locale, $module);
}
