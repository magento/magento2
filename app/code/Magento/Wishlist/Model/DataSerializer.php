<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Wishlist\Model;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Math\Random;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Service class to Serialize, Cache & Unserialize the data
 */
class DataSerializer
{
    /**
     * constant for Cache Key Prefix
     */
    private const CACHE_KEY_PREFIX = 'wishlist_';

    /**
     * constant for Cache Life time
     */
    private const CACHE_LIFE_TIME = 604800; //7 Days

    /**
     * constant for Cache Id length
     */
    private const CACHE_ID_LENGTH = 32;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var Random
     */
    private $random;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Initialize dependencies.
     *
     * @param SerializerInterface $serializer
     * @param CacheInterface $cache
     * @param Random $random
     * @param LoggerInterface $logger
     */
    public function __construct(
        SerializerInterface $serializer,
        CacheInterface $cache,
        Random $random,
        LoggerInterface $logger
    ) {
        $this->serializer = $serializer;
        $this->cache = $cache;
        $this->random = $random;
        $this->logger = $logger;
    }

    /**
     * Create Cache key, Serialize & Cache the provided data
     *
     * @param array $data
     * @return string
     */
    public function serialize(array $data): string
    {
        $token = $this->random->getRandomString(self::CACHE_ID_LENGTH);
        $cacheKey = self::CACHE_KEY_PREFIX.$token;

        $this->cache->save(
            $this->serializer->serialize($data),
            $cacheKey,
            [],
            self::CACHE_LIFE_TIME
        );

        return $token;
    }

    /**
     * Unserialize data for given Token
     *
     * @param string $token
     * @return array
     * @throws Throwable if unpredictable error occurred.
     */
    public function unserialize(string $token): array
    {
        $result = [];
        if (strlen($token) === self::CACHE_ID_LENGTH) {
            $cacheKey = self::CACHE_KEY_PREFIX.$token;
            $json = $this->cache->load($cacheKey);
            if ($json) {
                $result = $this->serializer->unserialize($json);
            }

            try {
                $this->cache->remove($token);
            } catch (Throwable $exception) {
                $this->logger->error('Unable to remove cache: '.$exception);
            }
        } else {
            $this->logger->error("Invalid Token '$token' supplied.");
        }

        return $result;
    }
}
