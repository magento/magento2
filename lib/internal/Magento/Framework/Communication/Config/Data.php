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
     * @param \Magento\Framework\Communication\Config\Reader\XmlReader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param \Magento\Framework\Communication\Config\Reader\EnvReader $envReader
     * @param \Magento\Framework\Communication\Config\Reader\RemoteServiceReader $remoteServiceReader
     * @param string $cacheId
     */
    public function __construct(
        \Magento\Framework\Communication\Config\Reader\XmlReader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        \Magento\Framework\Communication\Config\Reader\EnvReader $envReader,
        \Magento\Framework\Communication\Config\Reader\RemoteServiceReader $remoteServiceReader,
        $cacheId = 'communication_config_cache'
    ) {
        $this->merge($remoteServiceReader->read());
        parent::__construct($reader, $cache, $cacheId);
        $this->merge($envReader->read());
    }
}
