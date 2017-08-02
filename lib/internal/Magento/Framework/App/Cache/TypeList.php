<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Cache;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class \Magento\Framework\App\Cache\TypeList
 *
 * @since 2.0.0
 */
class TypeList implements TypeListInterface
{
    const INVALIDATED_TYPES = 'core_cache_invalidate';

    /**
     * @var \Magento\Framework\Cache\ConfigInterface
     * @since 2.0.0
     */
    protected $_config;

    /**
     * @var InstanceFactory
     * @since 2.0.0
     */
    protected $_factory;

    /**
     * @var StateInterface
     * @since 2.0.0
     */
    protected $_cacheState;

    /**
     * @var \Magento\Framework\App\CacheInterface
     * @since 2.0.0
     */
    protected $_cache;

    /**
     * @var SerializerInterface
     * @since 2.2.0
     */
    private $serializer;

    /**
     * @param \Magento\Framework\Cache\ConfigInterface $config
     * @param StateInterface $cacheState
     * @param InstanceFactory $factory
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param SerializerInterface $serializer
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Cache\ConfigInterface $config,
        StateInterface $cacheState,
        InstanceFactory $factory,
        \Magento\Framework\App\CacheInterface $cache,
        SerializerInterface $serializer = null
    ) {
        $this->_config = $config;
        $this->_factory = $factory;
        $this->_cacheState = $cacheState;
        $this->_cache = $cache;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
    }

    /**
     * Get cache class by cache type from configuration
     *
     * @param string $type
     * @return \Magento\Framework\Cache\FrontendInterface
     * @throws \UnexpectedValueException
     * @since 2.0.0
     */
    protected function _getTypeInstance($type)
    {
        $config = $this->_config->getType($type);
        if (!isset($config['instance'])) {
            return null;
        }
        return $this->_factory->get($config['instance']);
    }

    /**
     * Get invalidate types codes
     *
     * @return array
     * @since 2.0.0
     */
    protected function _getInvalidatedTypes()
    {
        $types = $this->_cache->load(self::INVALIDATED_TYPES);
        if ($types) {
            $types = $this->serializer->unserialize($types);
        } else {
            $types = [];
        }
        return $types;
    }

    /**
     * Save invalidated cache types
     *
     * @param array $types
     * @return void
     * @since 2.0.0
     */
    protected function _saveInvalidatedTypes($types)
    {
        $this->_cache->save($this->serializer->serialize($types), self::INVALIDATED_TYPES);
    }

    /**
     * Get information about all declared cache types
     *
     * @return array
     * @since 2.0.0
     */
    public function getTypes()
    {
        $types = [];
        $config = $this->_config->getTypes();

        foreach ($config as $type => $node) {
            $typeInstance = $this->_getTypeInstance($type);
            if ($typeInstance instanceof \Magento\Framework\Cache\Frontend\Decorator\TagScope) {
                $typeTags = $typeInstance->getTag();
            } else {
                $typeTags = '';
            }
            $types[$type] = new \Magento\Framework\DataObject(
                [
                    'id' => $type,
                    'cache_type' => $node['label'],
                    'description' => $node['description'],
                    'tags' => $typeTags,
                    'status' => (int)$this->_cacheState->isEnabled($type),
                ]
            );
        }
        return $types;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getTypeLabels()
    {
        $types = [];
        foreach ($this->_config->getTypes() as $type => $node) {
            if (array_key_exists('label', $node)) {
                $types[$type] = $node['label'];
            }
        }
        return $types;
    }

    /**
     * Get array of all invalidated cache types
     *
     * @return array
     * @since 2.0.0
     */
    public function getInvalidated()
    {
        $invalidatedTypes = [];
        $types = $this->_getInvalidatedTypes();
        if ($types) {
            $allTypes = $this->getTypes();
            foreach (array_keys($types) as $type) {
                if (isset($allTypes[$type]) && $this->_cacheState->isEnabled($type)) {
                    $invalidatedTypes[$type] = $allTypes[$type];
                }
            }
        }
        return $invalidatedTypes;
    }

    /**
     * Mark specific cache type(s) as invalidated
     *
     * @param string|array $typeCode
     * @return void
     * @since 2.0.0
     */
    public function invalidate($typeCode)
    {
        $types = $this->_getInvalidatedTypes();
        if (!is_array($typeCode)) {
            $typeCode = [$typeCode];
        }
        foreach ($typeCode as $code) {
            $types[$code] = 1;
        }
        $this->_saveInvalidatedTypes($types);
    }

    /**
     * Clean cached data for specific cache type
     *
     * @param string $typeCode
     * @return void
     * @since 2.0.0
     */
    public function cleanType($typeCode)
    {
        $this->_getTypeInstance($typeCode)->clean();
        $types = $this->_getInvalidatedTypes();
        unset($types[$typeCode]);
        $this->_saveInvalidatedTypes($types);
    }
}
