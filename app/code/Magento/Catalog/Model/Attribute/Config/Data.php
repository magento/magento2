<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\Catalog\Model\Attribute\Config;

use Magento\Framework\App\ObjectManager\ConfigWriterInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Provides catalog attributes configuration
 */
class Data extends \Magento\Framework\Config\Data
{
    /**
     * Constructor
     *
     * @param \Magento\Catalog\Model\Attribute\Config\Reader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string|null $cacheId
     * @param SerializerInterface|null $serializer
     * @param ConfigWriterInterface|null $configWriter
     * phpcs:disable Generic.CodeAnalysis.UselessOverridingMethod.Found
     */
    public function __construct(
        \Magento\Catalog\Model\Attribute\Config\Reader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'catalog_attributes',
        ?SerializerInterface $serializer = null,
        ?ConfigWriterInterface $configWriter = null
    ) {
        parent::__construct($reader, $cache, $cacheId, $serializer, null, $configWriter);
    }
}
