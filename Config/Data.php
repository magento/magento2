<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue\Config;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class for access to MessageQueue configuration data.
 */
class Data extends \Magento\Framework\Config\Data
{
    /**
     * Data constructor
     *
     * @param CompositeReader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param Reader\Env $envReader
     * @param Reader\Env\Validator $envValidator
     * @param string|null $cacheId
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        \Magento\Framework\MessageQueue\Config\CompositeReader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        \Magento\Framework\MessageQueue\Config\Reader\Env $envReader,
        \Magento\Framework\MessageQueue\Config\Reader\Env\Validator $envValidator,
        $cacheId = 'message_queue_config_cache',
        SerializerInterface $serializer = null
    ) {
        parent::__construct($reader, $cache, $cacheId, $serializer);
        $envValidator->validate($envReader->read(), $this->get());
    }
}
