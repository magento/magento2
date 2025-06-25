<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Rss\Model;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\FeedFactoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Rss\DataProviderInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\Serialize\SerializerInterface;

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
     * @var CacheInterface
     */
    protected $cache;

    /**
     * @var FeedFactoryInterface
     */
    private $feedFactory;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Rss constructor
     *
     * @param CacheInterface $cache
     * @param SerializerInterface|null $serializer
     * @param FeedFactoryInterface|null $feedFactory
     */
    public function __construct(
        CacheInterface $cache,
        ?SerializerInterface $serializer = null,
        ?FeedFactoryInterface $feedFactory = null
    ) {
        $this->cache = $cache;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
        $this->feedFactory = $feedFactory ?: ObjectManager::getInstance()->get(FeedFactoryInterface::class);
    }

    /**
     * Returns feeds
     *
     * @return array
     */
    public function getFeeds()
    {
        if ($this->dataProvider === null) {
            return [];
        }
        $cacheKey = $this->dataProvider->getCacheKey();
        $cacheLifeTime = $this->dataProvider->getCacheLifetime();

        $cache = $cacheKey && $cacheLifeTime ? $this->cache->load($cacheKey) : false;
        if ($cache) {
            return $this->serializer->unserialize($cache);
        }

        // serializing data to make sure all Phrase objects converted to a string
        $serializedData = $this->serializer->serialize($this->dataProvider->getRssData());

        if ($cacheKey && $cacheLifeTime) {
            $this->cache->save($serializedData, $cacheKey, $this->getCacheTags(), $cacheLifeTime);
        }

        return $this->serializer->unserialize($serializedData);
    }

    /**
     * Returns cache tags
     *
     * @return array|string[]
     */
    private function getCacheTags()
    {
        $tags = ['rss'];
        if ($this->dataProvider instanceof IdentityInterface) {
            $tags = array_merge($tags, $this->dataProvider->getIdentities());
        }
        return $tags;
    }

    /**
     * Sets data provider
     *
     * @param DataProviderInterface $dataProvider
     * @return $this
     */
    public function setDataProvider(DataProviderInterface $dataProvider)
    {
        $this->dataProvider = $dataProvider;

        return $this;
    }

    /**
     * Returns rss xml
     *
     * @return string
     * @throws InputException
     * @throws RuntimeException
     */
    public function createRssXml()
    {
        $feed = $this->feedFactory->create($this->getFeeds(), FeedFactoryInterface::FORMAT_RSS);

        return $feed->getFormattedContent();
    }
}
