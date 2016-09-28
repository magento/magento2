<?php
/**
 * Object manager configuration cache
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\ObjectManager;

use Magento\Framework\Json\JsonInterface;

class ConfigCache implements \Magento\Framework\ObjectManager\ConfigCacheInterface
{
    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    protected $_cacheFrontend;

    /**
     * Cache prefix
     *
     * @var string
     */
    protected $_prefix = 'diConfig';

    /**
     * @var JsonInterface
     */
    private $json;

    /**
     * @param \Magento\Framework\Cache\FrontendInterface $cacheFrontend
     */
    public function __construct(\Magento\Framework\Cache\FrontendInterface $cacheFrontend)
    {
        $this->_cacheFrontend = $cacheFrontend;
    }

    /**
     * Retrieve configuration from cache
     *
     * @param string $key
     * @return array
     */
    public function get($key)
    {
        return $this->getJson()->decode($this->_cacheFrontend->load($this->_prefix . $key));
    }

    /**
     * Save config to cache
     *
     * @param array $config
     * @param string $key
     * @return void
     */
    public function save(array $config, $key)
    {
        $this->_cacheFrontend->save($this->getJson()->encode($config), $this->_prefix . $key);
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
