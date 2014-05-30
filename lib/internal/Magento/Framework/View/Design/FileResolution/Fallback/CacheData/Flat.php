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
