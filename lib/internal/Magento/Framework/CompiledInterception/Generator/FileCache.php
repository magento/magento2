<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\CompiledInterception\Generator;


use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;

class FileCache implements CacheInterface, SerializerInterface
{

    private $cachePath;

    /**
     * FileCache constructor.
     * @param null $cachePath
     */
    public function __construct($cachePath = null)
    {
        $this->cachePath = ($cachePath === null ? BP . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'cache' : $cachePath);
    }


    /**
     * @param $identifier
     * @return string
     */
    private function getCachePath($identifier)
    {
        return $this->cachePath . DIRECTORY_SEPARATOR . str_replace('|', '_', $identifier . '.php');
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
     * @api
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
     * @api
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function save($data, $identifier, array $tags = [], $lifeTime = null)
    {
        if ($this->cachePath) {
            file_put_contents(
                $this->getCachePath($identifier),
                '<?php return ' . var_export($data, true) . '?>'
            );
        }
    }

    /**
     * Remove cache record by its unique identifier
     *
     * @param string $identifier
     * @return bool
     * @api
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
     * @api
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

    /**
     * Serialize data into string
     *
     * @param string|int|float|bool|array|null $data
     * @return string|bool
     * @throws \InvalidArgumentException
     * @since 101.0.0
     */
    public function serialize($data)
    {
        return $data;
    }

    /**
     * Unserialize the given string
     *
     * @param string $string
     * @return string|int|float|bool|array|null
     * @throws \InvalidArgumentException
     * @since 101.0.0
     */
    public function unserialize($string)
    {
        return $string;
    }
}