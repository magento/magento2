<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Event\Config;

use Magento\Framework\App\ObjectManager\ConfigWriterInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Provides event configuration
 */
class Data extends \Magento\Framework\Config\Data\Scoped
{
    /**
     * Scope priority loading scheme
     *
     * @var array
     */
    protected $_scopePriorityScheme = ['global'];

    /**
     * Constructor
     *
     * @param \Magento\Framework\Event\Config\Reader $reader
     * @param \Magento\Framework\Config\ScopeInterface $configScope
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string|null $cacheId
     * @param SerializerInterface|null $serializer
     * @param ConfigWriterInterface|null $configWriter
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found
     */
    public function __construct(
        \Magento\Framework\Event\Config\Reader $reader,
        \Magento\Framework\Config\ScopeInterface $configScope,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'event_config_cache',
        ?SerializerInterface $serializer = null,
        ?ConfigWriterInterface $configWriter = null
    ) {
        parent::__construct($reader, $configScope, $cache, $cacheId, $serializer, $configWriter);
    }
}
