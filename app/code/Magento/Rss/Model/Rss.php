<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rss\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Rss\DataProviderInterface;
use Magento\Framework\Serialize\SerializerInterface;

class Rss
{
    /**
     * @var DataProviderInterface
     */
    protected $dataProvider;

    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Rss constructor
     *
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        \Magento\Framework\App\CacheInterface $cache,
        SerializerInterface $serializer = null
    ) {
        $this->cache = $cache;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
    }

    /**
     * @return array
     */
    public function getFeeds()
    {
        if ($this->dataProvider === null) {
            return [];
        }
        $cache = false;
        if ($this->dataProvider->getCacheKey() && $this->dataProvider->getCacheLifetime()) {
            $cache = $this->cache->load($this->dataProvider->getCacheKey());
        }

        if ($cache) {
            return $this->serializer->unserialize($cache);
        }

        $data = $this->dataProvider->getRssData();

        if ($this->dataProvider->getCacheKey() && $this->dataProvider->getCacheLifetime()) {
            $this->cache->save(
                $this->serializer->serialize($data),
                $this->dataProvider->getCacheKey(),
                ['rss'],
                $this->dataProvider->getCacheLifetime()
            );
        }

        return $data;
    }

    /**
     * @param DataProviderInterface $dataProvider
     * @return $this
     */
    public function setDataProvider(DataProviderInterface $dataProvider)
    {
        $this->dataProvider = $dataProvider;
        return $this;
    }

    /**
     * @return string
     */
    public function createRssXml()
    {
        $rssFeedFromArray = \Zend_Feed::importArray($this->getFeeds(), 'rss');
        return $rssFeedFromArray->saveXML();
    }
}
