<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Structure;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Provides configuration
 * @api
 * @since 100.0.2
 */
class Data extends \Magento\Framework\Config\Data\Scoped
{
    /**
     * Constructor
     *
     * @param Reader $reader
     * @param \Magento\Framework\Config\ScopeInterface $configScope
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string $cacheId
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        Reader $reader,
        \Magento\Framework\Config\ScopeInterface $configScope,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId,
        ?SerializerInterface $serializer = null
    ) {
        parent::__construct($reader, $configScope, $cache, $cacheId, $serializer);
    }

    /**
     * Merge additional config
     *
     * @param array $config
     * @return void
     */
    public function merge(array $config)
    {
        if (isset($config['config']['system'])) {
            $config = $config['config']['system'];
        }
        parent::merge($config);
    }
}
