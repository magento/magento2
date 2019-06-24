<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\CompiledInterception\Generator;

use Magento\Framework\App\Filesystem\DirectoryList;
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
            $this->cachePath = BP . DIRECTORY_SEPARATOR .
                DirectoryList::GENERATED . DIRECTORY_SEPARATOR .
                'staticcache';
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
        return file_exists($this->getCachePath($identifier));
    }

    /**
     * Load cache record by its unique identifier
     *
     * @param string $identifier
     * @return string|bool
     * @SuppressWarnings(PHPMD)
     */
    public function load($identifier)
    {
        // @codingStandardsIgnoreLine
        return $this->cachePath ? @include $this->getCachePath($identifier) : false;
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
                mkdir(dirname($path), 0777, true);
            }
            file_put_contents(
                $path,
                '<?php return ' . var_export($data, true) . '?>'
            );
            return true;
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
        return false;
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
        if ($this->cachePath) {
            foreach (glob($this->cachePath . '/*') as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Retrieve backend instance
     *
     * @return \Zend_Cache_Backend_Interface
     */
    public function getBackend()
    {
        return null;
    }

    /**
     * Retrieve frontend instance compatible with Zend Locale Data setCache() to be used as a workaround
     *
     * @return \Zend_Cache_Core
     */
    public function getLowLevelFrontend()
    {
        return null;
    }
}
