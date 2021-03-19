<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Model\Storage\Handler;

use League\Flysystem\Config;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\FilesystemException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\RemoteStorage\Model\Storage\CacheStorage;
use Magento\RemoteStorage\Model\Storage\GetCleanedContents;

/**
 * Adapter cache model.
 */
class Local implements CacheStorageHandlerInterface
{
    /**
     * @var FilesystemAdapter
     */
    private $adapter;

    /**
     * @var string
     */
    private $file;

    /**
     * @var int|null
     */
    private $expire = null;

    /**
     * @var GetCleanedContents
     */
    private $getCleanedContents;

    /**
     * @var CacheStorage
     */
    private $cacheStorage;

    /**
     * @var Json
     */
    private $json;

    /**
     * @param GetCleanedContents $getCleanedContents
     * @param CacheStorage $cacheStorage
     * @param Json $json
     * @param FilesystemAdapter $adapter
     * @param string $file
     * @param null $expire
     */
    public function __construct(
        GetCleanedContents $getCleanedContents,
        CacheStorage $cacheStorage,
        Json $json,
        FilesystemAdapter $adapter,
        string $file,
        $expire = null
    ) {
        $this->json = $json;
        $this->adapter = $adapter;
        $this->file = $file;
        $this->cacheStorage = $cacheStorage;
        $this->setExpire($expire);
        $this->getCleanedContents = $getCleanedContents;
    }

    /**
     * @inheritdoc
     */
    public function load(): void
    {
        if ($this->adapter->fileExists($this->file)) {
            $contents = $this->adapter->read($this->file);
            if ($contents) {
                $this->setFromStorage($contents);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function save(): void
    {
        $config = new Config();
        $contents = $this->getForStorage();
        $this->adapter->write($this->file, $contents, $config);
    }

    /**
     * Set the expiration time in seconds.
     *
     * @param int|null $expire
     */
    private function setExpire(?int $expire): void
    {
        if ($expire) {
            $this->expire = $this->getTime($expire);
        }
    }

    /**
     * Retrieve serialized cache data.
     *
     * @return string
     */
    private function getForStorage(): string
    {
        $cleaned = $this->getCleanedContents->execute($this->cacheStorage->getCacheData());

        return $this->json->serialize([$cleaned, $this->cacheStorage->getCompleteData(), $this->expire]);
    }

    /**
     * Load from serialized cache data.
     *
     * @param string $json
     * @throws FilesystemException
     */
    private function setFromStorage(string $json): void
    {
        [$cache, $complete, $expire] = $this->json->unserialize($json);

        if (!$expire || $expire > $this->getTime()) {
            $cacheData = is_array($cache) ? $cache : [];
            $completeData = is_array($complete) ? $complete : [];
            $this->cacheStorage->setCacheData($cacheData);
            $this->cacheStorage->setCompleteData($completeData);
        } else {
            $this->adapter->delete($this->file);
        }
    }

    /**
     * Get expiration time in seconds.
     *
     * @param int $time
     * @return int
     */
    private function getTime($time = 0): int
    {
        return intval(microtime(true)) + $time;
    }
}
