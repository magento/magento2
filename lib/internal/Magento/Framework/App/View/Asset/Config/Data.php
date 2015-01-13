<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\App\View\Asset\Config;

class Data extends \Magento\Framework\Config\Data
{

    /**
     * @param Reader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string $cacheId
     */
    public function __construct(
        \Magento\Framework\App\View\Asset\Config\Reader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'bundle_config'
    ) {
        parent::__construct($reader, $cache, $cacheId);
    }

}
