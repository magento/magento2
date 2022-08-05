<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model\StoreSwitcher;

use InvalidArgumentException;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Math\Random;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Store switcher redirect data cache serializer
 */
class RedirectDataCacheSerializer implements RedirectDataSerializerInterface
{
    private const CACHE_KEY_PREFIX = 'store_switch_';
    private const CACHE_LIFE_TIME = 10;
    private const CACHE_ID_LENGTH = 32;

    /**
     * @var CacheInterface
     */
    private $cache;
    /**
     * @var Json
     */
    private $json;
    /**
     * @var Random
     */
    private $random;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Json $json
     * @param Random $random
     * @param CacheInterface $cache
     * @param LoggerInterface $logger
     */
    public function __construct(
        Json $json,
        Random $random,
        CacheInterface $cache,
        LoggerInterface $logger
    ) {
        $this->cache = $cache;
        $this->json = $json;
        $this->random = $random;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function serialize(array $data): string
    {
        $token = $this->random->getRandomString(self::CACHE_ID_LENGTH);
        $cacheKey = self::CACHE_KEY_PREFIX . $token;
        $this->cache->save($this->json->serialize($data), $cacheKey, [], self::CACHE_LIFE_TIME);

        return $token;
    }

    /**
     * @inheritDoc
     */
    public function unserialize(string $data): array
    {
        if (strlen($data) !== self::CACHE_ID_LENGTH) {
            throw new InvalidArgumentException("Invalid cache key '$data' supplied.");
        }

        $cacheKey = self::CACHE_KEY_PREFIX . $data;
        $json = $this->cache->load($cacheKey);
        if (!$json) {
            throw new InvalidArgumentException('Couldn\'t retrieve data from cache.');
        }
        $result = $this->json->unserialize($json);
        try {
            $this->cache->remove($cacheKey);
        } catch (Throwable $exception) {
            $this->logger->error($exception);
        }

        return $result;
    }
}
