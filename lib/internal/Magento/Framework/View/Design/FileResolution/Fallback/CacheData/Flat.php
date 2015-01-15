<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Design\FileResolution\Fallback\CacheData;

use Magento\Framework\View\Design\FileResolution\Fallback;

class Flat implements Fallback\CacheDataInterface
{
    /**
     * @var Fallback\Cache
     */
    private $cache;

    /**
     * @param Fallback\Cache $cache
     */
    public function __construct(Fallback\Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function getFromCache($type, $file, $area, $themePath, $locale, $module)
    {
        $cacheId = $this->getCacheId($type, $file, $area, $themePath, $locale, $module);
        return $this->cache->load($cacheId);
    }

    /**
     * {@inheritdoc}
     */
    public function saveToCache($value, $type, $file, $area, $themePath, $locale, $module)
    {
        $cacheId = $this->getCacheId($type, $file, $area, $themePath, $locale, $module);
        return $this->cache->save($value, $cacheId);
    }

    /**
     * Generate cache ID
     *
     * @param string $type
     * @param string $file
     * @param string $area
     * @param string $themePath
     * @param string $locale
     * @param string $module
     * @return string
     */
    protected function getCacheId($type, $file, $area, $themePath, $locale, $module)
    {
        return sprintf(
            "type:%s|area:%s|theme:%s|locale:%s|module:%s|file:%s",
            $type,
            $area,
            $themePath,
            $locale,
            $module,
            $file
        );
    }
}
