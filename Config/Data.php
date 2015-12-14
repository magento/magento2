<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\MessageQueue\Config;

/**
 * Class for access to MessageQueue configuration data.
 */
class Data extends \Magento\Framework\Config\Data
{
    /**
     * @var Validator
     */
    private $envValidator;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\MessageQueue\Config\Reader\XmlReader $xmlReader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param \Magento\Framework\MessageQueue\Config\Reader\EnvReader $envReader
     * @param \Magento\Framework\MessageQueue\Config\Reader\EnvReader\Validator $envValidator
     * @param string $cacheId
     */
    public function __construct(
        \Magento\Framework\MessageQueue\Config\Reader\XmlReader $xmlReader,
        \Magento\Framework\Config\CacheInterface $cache,
        \Magento\Framework\MessageQueue\Config\Reader\EnvReader $envReader,
        \Magento\Framework\MessageQueue\Config\Reader\EnvReader\ValidatorValidator $envValidator,
        $cacheId = 'message_queue_config_cache'
    ) {
        parent::__construct($xmlReader, $cache, $cacheId);

        $envConfigData = $envReader->read();
        $envValidator->validate($envConfigData, $this->get());
        $this->merge($envConfigData);
    }
}
