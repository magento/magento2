<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
