<?php
/**
 * Catalog attributes configuration data container. Provides catalog attributes configuration data.
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Attribute\Config;

class Data extends \Magento\Framework\Config\Data
{
    /**
     * @param \Magento\Catalog\Model\Attribute\Config\Reader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     */
    public function __construct(
        \Magento\Catalog\Model\Attribute\Config\Reader $reader,
        \Magento\Framework\Config\CacheInterface $cache
    ) {
        parent::__construct($reader, $cache, 'catalog_attributes');
    }
}
