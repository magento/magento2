<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\DataProvider\Config;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Config\Data as ConfigData;

/**
 * Class Data
 */
class Data extends ConfigData
{
    /**
     * Constructor
     *
     * @param Reader $reader
     * @param CacheInterface $cache
     */
    public function __construct(Reader $reader, CacheInterface $cache)
    {
        $this->cacheTags = [Attribute::CACHE_TAG];
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
