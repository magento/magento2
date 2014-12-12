<?php
/**
 * ObjectManager configuration loader
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\App\ObjectManager;

class ConfigLoader
{
    /**
     * Config reader
     *
     * @var \Magento\Framework\ObjectManager\Config\Reader\Dom
     */
    protected $_reader;

    /**
     * Config reader factory
     *
     * @var \Magento\Framework\ObjectManager\Config\Reader\DomFactory
     */
    protected $_readerFactory;

    /**
     * Cache
     *
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    protected $_cache;

    /**
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param \Magento\Framework\ObjectManager\Config\Reader\DomFactory $readerFactory
     */
    public function __construct(
        \Magento\Framework\Config\CacheInterface $cache,
        \Magento\Framework\ObjectManager\Config\Reader\DomFactory $readerFactory
    ) {
        $this->_cache = $cache;
        $this->_readerFactory = $readerFactory;
    }

    /**
     * Get reader instance
     *
     * @return \Magento\Framework\ObjectManager\Config\Reader\Dom
     */
    protected function _getReader()
    {
        if (empty($this->_reader)) {
            $this->_reader = $this->_readerFactory->create();
        }
        return $this->_reader;
    }

    /**
     * Load modules DI configuration
     *
     * @param string $area
     * @return array
     */
    public function load($area)
    {
        $cacheId = $area . '::DiConfig';
        $data = $this->_cache->load($cacheId);

        if (!$data) {
            $data = $this->_getReader()->read($area);
            $this->_cache->save(serialize($data), $cacheId);
        } else {
            $data = unserialize($data);
        }

        return $data;
    }
}
