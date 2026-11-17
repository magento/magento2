<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Backend\Model\Dashboard\Sales;

use Magento\Framework\App\Cache\Type\Block;
use Magento\Framework\Data\Collection\Db\FetchStrategy\Query;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Cache dashboard sales totals after collection filters have been rendered.
 */
class FetchStrategy implements FetchStrategyInterface
{
    /**
     * @param Query $query
     * @param Block $cache
     * @param Json $serializer
     * @param int $cacheTtl Cache lifetime in seconds; non-positive values disable caching.
     */
    public function __construct(
        private readonly Query $query,
        private readonly Block $cache,
        private readonly Json $serializer,
        private readonly int $cacheTtl = 0
    ) {
    }

    /**
     * @inheritdoc
     */
    public function fetchAll(Select $select, array $bindParams = [])
    {
        if ($this->cacheTtl <= 0) {
            return $this->query->fetchAll($select, $bindParams);
        }

        $cacheId = 'dashboard_sales_' . hash(
            'sha256',
            $this->serializer->serialize([(string)$select, $bindParams])
        );
        $cachedData = $this->cache->load($cacheId);
        if ($cachedData !== false) {
            try {
                $data = $this->serializer->unserialize($cachedData);
            } catch (\InvalidArgumentException) {
                $data = null;
            }
            if (isset($data[0])
                && is_array($data[0])
                && array_key_exists('lifetime', $data[0])
                && array_key_exists('average', $data[0])
            ) {
                return $data;
            }
        }

        $data = $this->query->fetchAll($select, $bindParams);
        $this->cache->save($this->serializer->serialize($data), $cacheId, [], $this->cacheTtl);

        return $data;
    }
}
