<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\Index\Config;

use Magento\Framework\Config\Data;
use Magento\Framework\Config\CacheInterface;

class EsConfig extends Data implements EsConfigInterface
{
    /**
     * @param Reader $reader
     * @param CacheInterface $cache
     * @param string $cacheId
     */
    public function __construct(
        Reader $reader,
        CacheInterface $cache,
        $cacheId = 'elasticsearch_index_config'
    ) {
        parent::__construct($reader, $cache, $cacheId);
    }

    /**
     * {@inheritdoc}
     */
    public function getStemmerInfo()
    {
        return $this->get();
    }
}
