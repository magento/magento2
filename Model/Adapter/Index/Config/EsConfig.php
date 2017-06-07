<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Elasticsearch\Model\Adapter\Index\Config;

use Magento\Framework\Config\Data;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\Serialize\SerializerInterface;

class EsConfig extends Data implements EsConfigInterface
{
    /**
     * @param ReaderInterface $reader
     * @param CacheInterface $cache
     * @param string $cacheId
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        ReaderInterface $reader,
        CacheInterface $cache,
        $cacheId,
        SerializerInterface $serializer = null
    ) {
        parent::__construct($reader, $cache, $cacheId, $serializer);
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
