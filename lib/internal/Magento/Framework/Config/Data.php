<?php
/**
 * Config data. Represents loaded and cached configuration data. Should be used to gain access to different types
 *
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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Config;

class Data implements \Magento\Framework\Config\DataInterface
{
    /**
     * Configuration reader model
     *
     * @var ReaderInterface
     */
    protected $_reader;

    /**
     * Configuration cache model
     *
     * @var CacheInterface
     */
    protected $_cache;

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheId;

    /**
     * Config data
     *
     * @var array
     */
    protected $_data = [];

    /**
     * @var ReaderInterface
     */
    private $reader;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var string
     */
    private $cacheId;

    /**
     * Constructor
     *
     * @param ReaderInterface $reader
     * @param CacheInterface $cache
     * @param string $cacheId
     */
    public function __construct(
        ReaderInterface $reader,
        CacheInterface $cache,
        $cacheId
    ) {
        $this->reader = $reader;
        $this->cache = $cache;
        $this->cacheId = $cacheId;
        $this->initData();
    }

    /**
     * Initialise data for configuration
     * @return void
     */
    protected function initData()
    {
        $data = $this->cache->load($this->cacheId);
        if (false === $data) {
            $data = $this->reader->read();
            $this->cache->save(serialize($data), $this->cacheId);
        } else {
            $data = unserialize($data);
        }
        $this->merge($data);
    }

    /**
     * Merge config data to the object
     *
     * @param array $config
     * @return void
     */
    public function merge(array $config)
    {
        $this->_data = array_replace_recursive($this->_data, $config);
    }

    /**
     * Get config value by key
     *
     * @param string $path
     * @param mixed $default
     * @return array|mixed|null
     */
    public function get($path = null, $default = null)
    {
        if ($path === null) {
            return $this->_data;
        }
        $keys = explode('/', $path);
        $data = $this->_data;
        foreach ($keys as $key) {
            if (is_array($data) && array_key_exists($key, $data)) {
                $data = $data[$key];
            } else {
                return $default;
            }
        }
        return $data;
    }

    /**
     * Clear cache data
     * @return void
     */
    public function reset()
    {
        $this->cache->remove($this->cacheId);
    }
}
