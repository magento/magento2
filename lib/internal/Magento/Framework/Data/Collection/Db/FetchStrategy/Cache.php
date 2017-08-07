<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Data\Collection\Db\FetchStrategy;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Select;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Retrieve collection data from cache, fail over to another fetch strategy, if cache does not exist yet
 */
class Cache implements \Magento\Framework\Data\Collection\Db\FetchStrategyInterface
{
    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    private $_cache;

    /**
     * @var \Magento\Framework\Data\Collection\Db\FetchStrategyInterface
     */
    private $_fetchStrategy;

    /**
     * @var string
     */
    protected $_cacheIdPrefix = '';

    /**
     * @var array
     */
    protected $_cacheTags = [];

    /**
     * @var int|bool|null
     */
    protected $_cacheLifetime = null;

    /**
     * @var SerializerInterface
     * @since 2.2.0
     */
    private $serializer;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Cache\FrontendInterface $cache
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param string $cacheIdPrefix
     * @param array $cacheTags
     * @param int|bool|null $cacheLifetime
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        \Magento\Framework\Cache\FrontendInterface $cache,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        $cacheIdPrefix = '',
        array $cacheTags = [],
        $cacheLifetime = null,
        SerializerInterface $serializer = null
    ) {
        $this->_cache = $cache;
        $this->_fetchStrategy = $fetchStrategy;
        $this->_cacheIdPrefix = $cacheIdPrefix;
        $this->_cacheTags = $cacheTags;
        $this->_cacheLifetime = $cacheLifetime;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAll(Select $select, array $bindParams = [])
    {
        $cacheId = $this->_getSelectCacheId($select);
        $result = $this->_cache->load($cacheId);
        if ($result) {
            $result = $this->serializer->unserialize($result);
        } else {
            $result = $this->_fetchStrategy->fetchAll($select, $bindParams);
            $this->_cache->save(
                $this->serializer->serialize($result),
                $cacheId,
                $this->_cacheTags,
                $this->_cacheLifetime
            );
        }
        return $result;
    }

    /**
     * Determine cache identifier based on select query
     *
     * @param \Magento\Framework\DB\Select|string $select
     * @return string
     */
    protected function _getSelectCacheId($select)
    {
        return $this->_cacheIdPrefix . md5((string)$select);
    }
}
