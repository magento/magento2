<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rss\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Rss\DataProviderInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\FeedImporterInterface;

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
     * @var \Magento\Framework\App\FeedImporterInterface
     */
    private $feedImporter;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Rss constructor
     *
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param SerializerInterface|null $serializer
     * @param FeedImporterInterface|null $feedImporter
     */
    public function __construct(
        \Magento\Framework\App\CacheInterface $cache,
        SerializerInterface $serializer = null,
        FeedImporterInterface $feedImporter = null
    ) {
        $this->cache = $cache;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
        $this->feedImporter = $feedImporter ?: ObjectManager::getInstance()->get(FeedImporterInterface::class);
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
        $rssFeed = $this->feedImporter->importArray($this->getFeeds(), 'rss');
        return $rssFeed->asXML();
    }
}
