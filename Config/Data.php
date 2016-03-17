<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Config;

/**
 * Class for access to MessageQueue configuration data.
 */
class Data extends \Magento\Framework\Config\Data
{
    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\MessageQueue\Config\CompositeReader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param \Magento\Framework\MessageQueue\Config\Reader\Env $envReader
     * @param \Magento\Framework\MessageQueue\Config\Reader\Env\Validator $envValidator
     * @param string $cacheId
     */
    public function __construct(
        \Magento\Framework\MessageQueue\Config\CompositeReader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        \Magento\Framework\MessageQueue\Config\Reader\Env $envReader,
        \Magento\Framework\MessageQueue\Config\Reader\Env\Validator $envValidator,
        $cacheId = 'message_queue_config_cache'
    ) {
        parent::__construct($reader, $cache, $cacheId);
        $envValidator->validate($envReader->read(), $this->get());
    }
}
