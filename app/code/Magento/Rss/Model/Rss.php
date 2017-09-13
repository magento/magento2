<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rss\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Rss\DataProviderInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\FeedFactoryInterface;

/**
 * Provides functionality to work with RSS feeds
 *
 * @api
 * @since 100.0.2
 */
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
     * @var \Magento\Framework\App\FeedFactoryInterface
     */
    private $feedFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Rss constructor
     *
     * @param \Magento\Framework\App\CacheInterface $cache
     * @param SerializerInterface|null $serializer
     * @param FeedFactoryInterface|null $feedFactory
     */
    public function __construct(
        \Magento\Framework\App\CacheInterface $cache,
        SerializerInterface $serializer = null,
        FeedFactoryInterface $feedFactory = null
    ) {
        $this->cache = $cache;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
        $this->feedFactory = $feedFactory ?: ObjectManager::getInstance()->get(FeedFactoryInterface::class);
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
        $feed = $this->feedFactory->create(
            $this->getFeeds(),
            \Magento\Framework\App\FeedFactoryInterface::DEFAULT_FORMAT
        );

        return $feed->getFormatedContentAs(
            \Magento\Framework\App\FeedInterface::DEFAULT_FORMAT
        );
    }
}
