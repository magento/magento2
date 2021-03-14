<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\CompiledInterception\Generator;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Config\CacheInterface;

/**
 * Files cache management.
 */
class FileCache implements CacheInterface
{
    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var string|null
     */
    private $cachePath;

    /**
     * @param DirectoryList $directoryList
     * @param ?string $cachePath
     */
    public function __construct(
        DirectoryList $directoryList,
        $cachePath = null
    ) {
        $this->directoryList = $directoryList;
        $this->cachePath = $cachePath;
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
        return file_exists($this->getCacheFilePath($identifier));
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
        return $this->getCachePath() ? @include $this->getCacheFilePath($identifier) : false;
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
        if ($this->getCachePath()) {
            $path = $this->getCacheFilePath($identifier);
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
        if ($this->getCachePath()) {
            foreach (glob($this->getCachePath() . '/*') as $file) {
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

    /**
     * Get file cache path.
     *
     * @param string $identifier
     * @return string
     */
    private function getCacheFilePath($identifier): string
    {
        $identifier = str_replace('|', '_', $identifier);
        return $this->getCachePath() . DIRECTORY_SEPARATOR . $identifier . '.php';
    }

    /**
     * Get cache path.
     *
     * @return string
     */
    private function getCachePath(): string
    {
        if (!$this->cachePath) {
            $this->cachePath = $this->directoryList->getPath(DirectoryList::STATIC_CACHE);
        }

        return $this->cachePath;
    }
}
