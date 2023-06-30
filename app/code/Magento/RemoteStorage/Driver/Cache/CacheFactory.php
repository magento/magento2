<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Driver\Cache;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Cached\CacheInterface;
use League\Flysystem\Cached\Storage\Memory;
use League\Flysystem\Cached\Storage\Predis;
use League\Flysystem\Cached\Storage\Adapter;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\RemoteStorage\Driver\DriverException;
use Magento\RemoteStorage\Driver\DriverPool;
use Predis\Client;

/**
 * Provides cache adapters.
 */
class CacheFactory
{
    public const ADAPTER_PREDIS = 'predis';
    public const ADAPTER_MEMORY = 'memory';
    public const ADAPTER_LOCAL = 'local';

    private const CACHE_KEY = 'storage';
    private const CACHE_FILE = 'storage_cache.json';

    /**
     * Cache for 30 days.
     */
    private const CACHE_EXPIRATION = 30 * 86400;

    /**
     * @var string
     */
    private $localCacheRoot;

    /**
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->localCacheRoot = $filesystem->getDirectoryRead(
            DirectoryList::VAR_DIR,
            DriverPool::FILE
        )->getAbsolutePath();
    }

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
            case self::ADAPTER_LOCAL:
                return new Adapter(new Local($this->localCacheRoot), self::CACHE_FILE, self::CACHE_EXPIRATION);
        }

        throw new DriverException(__('Cache adapter %1 is not supported', $adapter));
    }
}
