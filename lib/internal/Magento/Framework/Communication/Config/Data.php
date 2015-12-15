<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Communication\Config;

/**
 * Communication config data.
 */
class Data extends \Magento\Framework\Config\Data
{
    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Communication\Config\Reader\XmlReader $xmlReader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param \Magento\Framework\Communication\Config\Reader\EnvReader $envReader
     * @param \Magento\Framework\Communication\Config\Reader\RemoteServiceReader $remoteServiceReader
     * @param string $cacheId
     */
    public function __construct(
        \Magento\Framework\Communication\Config\Reader\XmlReader $xmlReader,
        \Magento\Framework\Config\CacheInterface $cache,
        \Magento\Framework\Communication\Config\Reader\EnvReader $envReader,
        \Magento\Framework\Communication\Config\Reader\RemoteServiceReader $remoteServiceReader,
        $cacheId = 'communication_config_cache'
    ) {
        $this->merge($remoteServiceReader->read());
        parent::__construct($xmlReader, $cache, $cacheId);
        $this->merge($envReader->read());
    }
}
