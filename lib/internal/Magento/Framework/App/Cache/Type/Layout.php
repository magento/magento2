<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Cache\Type;

/**
 * System / Cache Management / Cache type "Layouts"
 */
class Layout extends \Magento\Framework\Cache\Frontend\Decorator\TagScope
{
    /**
     * Prefix for hash kay and hash data
     */
    public const HASH_PREFIX = 'l:';

    /**
     * Hash type, not used for security, only for uniqueness
     */
    public const HASH_TYPE = 'xxh3';

    /**
     * Data lifetime in milliseconds
     */
    public const DATA_LIFETIME = 86_400_000; // "1 day" milliseconds

    /**
     * Cache type code unique among all cache types
     */
    public const TYPE_IDENTIFIER = 'layout';

    /**
     * Cache tag used to distinguish the cache type from all other cache
     */
    public const CACHE_TAG = 'LAYOUT_GENERAL_CACHE_TAG';

    /**
     * @param FrontendPool $cacheFrontendPool
     */
    public function __construct(FrontendPool $cacheFrontendPool)
    {
        parent::__construct($cacheFrontendPool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
    }

    /**
     * @inheritDoc
     */
    public function save($data, $identifier, array $tags = [], $lifeTime = null)
    {
        $dataHash = hash(self::HASH_TYPE, $data);
        $identifierForHash = self::HASH_PREFIX . $dataHash;
        return parent::save($data, $identifierForHash, $tags, self::DATA_LIFETIME) // key is hash of data hash
            && parent::save(self::HASH_PREFIX . $dataHash, $identifier, $tags, $lifeTime); // store hash of data
    }

    /**
     * @inheritDoc
     */
    public function load($identifier)
    {
        $data = parent::load($identifier);
        if ($data === false || $data === null) {
            return $data;
        }

        if (str_starts_with($data, self::HASH_PREFIX)) {
            // so data stored in other place
            return parent::load($data);
        } else {
            return $data;
        }
    }
}
