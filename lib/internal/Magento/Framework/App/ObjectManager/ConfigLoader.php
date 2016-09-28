<?php
/**
 * ObjectManager configuration loader
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\ObjectManager;

use Magento\Framework\Json\JsonInterface;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;

class ConfigLoader implements ConfigLoaderInterface
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
     * @var JsonInterface
     */
    private $json;

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
     * {inheritdoc}
     */
    public function load($area)
    {
        $cacheId = $area . '::DiConfig';
        $data = $this->_cache->load($cacheId);

        if (!$data) {
            $data = $this->_getReader()->read($area);
            $this->_cache->save($this->getJson()->encode($data), $cacheId);
        } else {
            $data = $this->getJson()->decode($data);
        }

        return $data;
    }

    /**
     * Get json encoder/decoder
     *
     * @return JsonInterface
     * @deprecated
     */
    private function getJson()
    {
        if ($this->json === null) {
            $this->json = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(JsonInterface::class);
        }
        return $this->json;
    }
}
