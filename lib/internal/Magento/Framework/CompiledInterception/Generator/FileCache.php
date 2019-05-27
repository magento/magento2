<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\CompiledInterception\Generator;

use Magento\Framework\Config\CacheInterface;

/**
 * Class FileCache
 */
class FileCache implements CacheInterface
{

    private $cachePath;

    /**
     * FileCache constructor.
     * @param null|string $cachePath
     */
    public function __construct($cachePath = null)
    {
        if ($cachePath === null) {
            $this->cachePath = BP . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'cache';
        } else {
            $this->cachePath = $cachePath;
        }
    }

    /**
     * Get cache path
     *
     * @param string $identifier
     * @return string
     */
    private function getCachePath($identifier)
    {
        $identifier = str_replace('|', '_', $identifier);
        return $this->cachePath . DIRECTORY_SEPARATOR . $identifier . '.php';
    }

    /**
     * Test if a cache is available for the given id
     *
     * @param string $identifier Cache id
     * @return int|bool Last modified time of cache entry if it is available, false otherwise
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function test($identifier)
    {
        // TODO: Implement test() method.
    }

    /**
     * Load cache record by its unique identifier
     *
     * @param string $identifier
     * @return string|bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function load($identifier)
    {
        return $this->cachePath ? @include($this->getCachePath($identifier)) : false;
    }

    /**
     * Save cache record
     *
     * @param string $data
     * @param string $identifier
     * @param array $tags
     * @param int|bool|null $lifeTime
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function save($data, $identifier, array $tags = [], $lifeTime = null)
    {
        if ($this->cachePath) {
            $path = $this->getCachePath($identifier);
            if (!is_dir(dirname($path))) {
                mkdir(dirname($path));
            }
            file_put_contents(
                $path,
                '<?php return ' . var_export($data, true) . '?>'
            );
        }
    }

    /**
     * Remove cache record by its unique identifier
     *
     * @param string $identifier
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function remove($identifier)
    {
        // TODO: Implement remove() method.
    }

    /**
     * Clean cache records matching specified tags
     *
     * @param string $mode
     * @param array $tags
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function clean($mode = \Zend_Cache::CLEANING_MODE_ALL, array $tags = [])
    {
        // TODO: Implement clean() method.
    }

    /**
     * Retrieve backend instance
     *
     * @return \Zend_Cache_Backend_Interface
     */
    public function getBackend()
    {
        // TODO: Implement getBackend() method.
    }

    /**
     * Retrieve frontend instance compatible with Zend Locale Data setCache() to be used as a workaround
     *
     * @return \Zend_Cache_Core
     */
    public function getLowLevelFrontend()
    {
        // TODO: Implement getLowLevelFrontend() method.
    }
}
