<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Design\FileResolution\Fallback\CacheData;

use Magento\Framework\View\Design\FileResolution\Fallback;

class Grouped implements Fallback\CacheDataInterface
{
    /**
     * @var Fallback\Cache
     */
    private $cache;

    /**
     * @var string[]
     */
    private $isDirty = [];

    /**
     * @var array
     */
    private $cacheSections = [];

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
        $sectionId = $this->getCacheSectionId($type, $area, $themePath, $locale);
        $this->loadSection($sectionId);
        $recordId = $this->getCacheRecordId($file, $module);
        if (!isset($this->cacheSections[$sectionId][$recordId])) {
            $this->cacheSections[$sectionId][$recordId] = false;
        }
        return $this->cacheSections[$sectionId][$recordId];
    }

    /**
     * {@inheritdoc}
     */
    public function saveToCache($value, $type, $file, $area, $themePath, $locale, $module)
    {
        $sectionId = $this->getCacheSectionId($type, $area, $themePath, $locale);
        $this->loadSection($sectionId);
        $recordId = $this->getCacheRecordId($file, $module);
        if (!isset($this->cacheSections[$sectionId][$recordId])
            || $this->cacheSections[$sectionId][$recordId] !== $value) {
            $this->isDirty[$sectionId] = $sectionId;
            $this->cacheSections[$sectionId][$recordId] = $value;
        }
        return true;
    }

    /**
     * @param string $sectionId
     * @return void
     */
    private function loadSection($sectionId)
    {
        if (!isset($this->cacheSections[$sectionId])) {
            $value = $this->cache->load($sectionId);
            if ($value) {
                $this->cacheSections[$sectionId] = json_decode($value, true);
            } else {
                $this->cacheSections[$sectionId] = [];
            }
        }
    }

    /**
     * Generate section ID
     *
     * @param string $type
     * @param string $area
     * @param string $themePath
     * @param string $locale
     *
     * @return string
     */
    protected function getCacheSectionId($type, $area, $themePath, $locale)
    {
        return sprintf(
            "type:%s|area:%s|theme:%s|locale:%s",
            $type,
            $area,
            $themePath,
            $locale
        );
    }

    /**
     * Generate record ID
     *
     * @param string $file
     * @param string $module
     * @return string
     */
    protected function getCacheRecordId($file, $module)
    {
        return sprintf("module:%s|file:%s", $module, $file);
    }

    /**
     * Save cache
     */
    public function __destruct()
    {
        foreach ($this->isDirty as $sectionId) {
            $value = json_encode($this->cacheSections[$sectionId]);
            $this->cache->save($value, $sectionId);
        }
    }
}
