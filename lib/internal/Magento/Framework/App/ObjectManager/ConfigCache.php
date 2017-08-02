<?php
/**
 * Object manager configuration cache
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\ObjectManager;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Serialize\Serializer\Serialize;

/**
 * Class \Magento\Framework\App\ObjectManager\ConfigCache
 *
 * @since 2.0.0
 */
class ConfigCache implements \Magento\Framework\ObjectManager\ConfigCacheInterface
{
    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     * @since 2.0.0
     */
    protected $_cacheFrontend;

    /**
     * Cache prefix
     *
     * @var string
     * @since 2.0.0
     */
    protected $_prefix = 'diConfig';

    /**
     * @var SerializerInterface
     * @since 2.2.0
     */
    private $serializer;

    /**
     * @param \Magento\Framework\Cache\FrontendInterface $cacheFrontend
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\Cache\FrontendInterface $cacheFrontend)
    {
        $this->_cacheFrontend = $cacheFrontend;
    }

    /**
     * Retrieve configuration from cache
     *
     * @param string $key
     * @return array|false
     * @since 2.0.0
     */
    public function get($key)
    {
        $data = $this->_cacheFrontend->load($this->_prefix . $key);
        if (!$data) {
            return false;
        }
        return $this->getSerializer()->unserialize($data);
    }

    /**
     * Save config to cache
     *
     * @param array $config
     * @param string $key
     * @return void
     * @since 2.0.0
     */
    public function save(array $config, $key)
    {
        $this->_cacheFrontend->save($this->getSerializer()->serialize($config), $this->_prefix . $key);
    }

    /**
     * Get serializer
     *
     * @return SerializerInterface
     * @deprecated 2.2.0
     * @since 2.2.0
     */
    private function getSerializer()
    {
        if (null === $this->serializer) {
            $this->serializer = \Magento\Framework\App\ObjectManager::getInstance()->get(Serialize::class);
        }
        return $this->serializer;
    }
}
