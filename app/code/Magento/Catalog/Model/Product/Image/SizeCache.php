<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Image;

use Magento\Framework\App\CacheInterface;

/**
 * Image size cache
 */
class SizeCache
{
    /**
     * @var string
     */
    private $cachePrefix = 'IMG_INFO';

    /**
     * Application Cache Manager
     *
     * @var CacheInterface
     */
    protected $cacheManager;

    /**
     * SizeCache constructor.
     * @param CacheInterface $cacheManager
     */
    public function __construct(
        CacheInterface $cacheManager
    ) {
        $this->cacheManager = $cacheManager;
    }

    /**
     * Save image size to cache
     *
     * @param int $width
     * @param int $height
     * @param string $path
     * @return void
     */
    public function save($width, $height, $path)
    {
        $this->cacheManager->save(
            serialize(['width' => $width, 'height' => $height]),
            $this->cachePrefix . $path
        );
    }

    /**
     * Load image size from cache
     *
     * @param string $path
     * @return array ['width' => '...', 'height' => '...']
     */
    public function load($path)
    {
        $key = $this->cachePrefix . $path;
        $size = $this->cacheManager->load($key);
        $size = $size ? unserialize($size) : null;

        return $size;
    }
}
