<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Driver\Cache;

use League\Flysystem\Cached\CacheInterface;
use League\Flysystem\Cached\Storage\Memory;
use League\Flysystem\Cached\Storage\Predis;
use Magento\RemoteStorage\Driver\DriverException;
use Predis\Client;

/**
 * Provides cache adapters.
 */
class CacheFactory
{
    public const ADAPTER_PREDIS = 'predis';
    public const ADAPTER_MEMORY = 'memory';

    private const CACHE_KEY = 'storage';

    /**
     * Cache for 30 days.
     */
    private const CACHE_EXPIRATION = 30 * 86400;

    /**
     * Create cache adapter.
     *
     * @param string $adapter
     * @param array $config
     * @return CacheInterface
     * @throws DriverException
     */
    public function create(string $adapter, array $config = []): CacheInterface
    {
        switch ($adapter) {
            case self::ADAPTER_PREDIS:
                if (!class_exists(Client::class)) {
                    throw new DriverException(__('Predis client is not installed'));
                }

                return new Predis(new Client($config), self::CACHE_KEY, self::CACHE_EXPIRATION);
            case self::ADAPTER_MEMORY:
                return new Memory();
        }

        throw new DriverException(__('Cache adapter %1 is not supported', $adapter));
    }
}
