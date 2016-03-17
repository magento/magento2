<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\Index\Config;

use Magento\Framework\Config\Data;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Config\ReaderInterface;

class EsConfig extends Data implements EsConfigInterface
{
    /**
     * @param ReaderInterface $reader
     * @param CacheInterface $cache
     * @param string $cacheId
     */
    public function __construct(
        ReaderInterface $reader,
        CacheInterface $cache,
        $cacheId
    ) {
        parent::__construct($reader, $cache, $cacheId);
    }

    /**
     * {@inheritdoc}
     */
    public function getStemmerInfo()
    {
        return $this->get('stemmerInfo');
    }

    /**
     * {@inheritdoc}
     */
    public function getStopwordsInfo()
    {
        return $this->get('stopwordsInfo');
    }
}
