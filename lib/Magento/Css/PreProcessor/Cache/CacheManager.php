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

namespace Magento\Css\PreProcessor\Cache;

class CacheManager
{
    /**
     * @var CacheFactory
     */
    protected $cacheFactory;

    /**
     * @var CacheInterface[]
     */
    protected $cacheByType = [];

    /**
     * @param CacheFactory $cacheFactory
     */
    public function __construct(
        CacheFactory $cacheFactory
    ) {
        $this->cacheFactory = $cacheFactory;
    }

    /**
     * @param string $cacheType
     * @param \Magento\View\Publisher\FileInterface $publisherFile
     * @return $this
     */
    public function initializeCacheByType($cacheType, $publisherFile)
    {
        $this->cacheByType[$cacheType] = $this->cacheFactory->create($cacheType, $publisherFile);
        return $this;
    }

    /**
     * @param string $cacheType
     * @return string|null
     */
    public function getCachedFile($cacheType)
    {
        return $this->isCacheInitialized($cacheType) ? $this->cacheByType[$cacheType]->get() : null;
    }

    /**
     * @param string $cacheType
     * @param \Magento\Less\PreProcessor\File\Less $lessFile
     * @return $this
     */
    public function addToCache($cacheType, $lessFile)
    {
        !$this->isCacheInitialized($cacheType) ?: $this->cacheByType[$cacheType]->add($lessFile);
        return $this;
    }

    /**
     * @param string $cacheType
     * @param string $cacheFile
     * @return $this
     */
    public function saveCache($cacheType, $cacheFile)
    {
        !$this->isCacheInitialized($cacheType) ?: $this->cacheByType[$cacheType]->save($cacheFile);
        return $this;
    }

    /**
     * @param string $cacheType
     * @return $this
     */
    public function clearCache($cacheType)
    {
        !$this->isCacheInitialized($cacheType) ?: $this->cacheByType[$cacheType]->clear();
        return $this;
    }

    /**
     * @param string $cacheType
     * @return bool
     */
    public function isCacheInitialized($cacheType)
    {
        return isset($this->cacheByType[$cacheType]);
    }
}
