<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Ui\DataProvider\Config;

/**
 * Class Data
 */
class Data extends \Magento\Framework\Config\Data
{
    /**
     * @param Reader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     */
    public function __construct(
        Reader $reader,
        \Magento\Framework\Config\CacheInterface $cache
    ) {
        $this->cacheTags = [\Magento\Eav\Model\Entity\Attribute::CACHE_TAG];
        parent::__construct($reader, $cache, 'data_source');
    }

    /**
     * @param string $name
     * @return array|mixed|null
     */
    public function getDataSource($name)
    {
        return $this->get($name);
    }
}
