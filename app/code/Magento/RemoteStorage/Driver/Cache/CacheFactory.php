<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Driver\Cache;

use League\Flysystem\Local\LocalFilesystemAdapter;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\RemoteStorage\Driver\DriverException;
use Magento\RemoteStorage\Driver\DriverPool;
use Magento\RemoteStorage\Model\Storage\Handler\LocalFactory;
use Magento\RemoteStorage\Model\Storage\Handler\MemoryFactory;
use Predis\Client;
use Magento\RemoteStorage\Model\Storage\Handler\PredisFactory;

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
     * @var MemoryFactory
     */
    private MemoryFactory $memoryFactory;

    /**
     * @var LocalFactory
     */
    private LocalFactory $localFactory;

    /**
     * @var PredisFactory
     */
    private PredisFactory $predisFactory;

    /**
     * @var CacheInterfaceFactory
     */
    private CacheInterfaceFactory $cacheFactory;

    /**
     * @param CacheInterfaceFactory $cacheFactory
     * @param Filesystem $filesystem
     * @param MemoryFactory $memoryFactory
     * @param LocalFactory $localFactory
     * @param PredisFactory $predisFactory
     */
    public function __construct(
        CacheInterfaceFactory $cacheFactory,
        Filesystem $filesystem,
        MemoryFactory $memoryFactory,
        LocalFactory $localFactory,
        PredisFactory $predisFactory
    ) {
        $this->localCacheRoot = $filesystem->getDirectoryRead(
            DirectoryList::VAR_DIR,
            DriverPool::FILE
        )->getAbsolutePath();
        $this->memoryFactory = $memoryFactory;
        $this->localFactory = $localFactory;
        $this->predisFactory = $predisFactory;
        $this->cacheFactory = $cacheFactory;
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
                $predis =  $this->predisFactory->create(
                    [
                        'client' => new Client($config),
                        'key' => self::CACHE_KEY,
                        'expire' => self::CACHE_EXPIRATION,
                    ]
                );
                return $this->cacheFactory->create(['cacheStorageHandler' => $predis]);
            case self::ADAPTER_MEMORY:
                $memory = $this->memoryFactory->create();
                return $this->cacheFactory->create(['cacheStorageHandler' => $memory]);
            case self::ADAPTER_LOCAL:
                $local = $this->localFactory->create(
                    [
                        'adapter' => new LocalFilesystemAdapter($this->localCacheRoot),
                        'file' => self::CACHE_FILE,
                        'expire' => self::CACHE_EXPIRATION,
                    ]
                );
                return $this->cacheFactory->create(['cacheStorageHandler' => $local]);
        }

        throw new DriverException(__('Cache adapter %1 is not supported', $adapter));
    }
}
