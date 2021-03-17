<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Model\Storage\Handler;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\RemoteStorage\Model\Storage\CacheStorage;
use Magento\RemoteStorage\Model\Storage\GetCleanedContents;
use Predis\Client;

/**
 * Redis cache model.
 */
class Predis implements CacheStorageHandlerInterface
{
    /**
     * @var string
     */
    private string $key;

    /**
     * @var int|null
     */
    private ?int $expire;

    /**
     * @var Json
     */
    private Json $json;

    /**
     * @var GetCleanedContents
     */
    private GetCleanedContents $getCleanedContents;

    /**
     * @var CacheStorage
     */
    private CacheStorage $cacheStorage;

    /**
     * @var Client
     */
    private Client $client;

    /**
     * @param CacheStorage $cacheStorage
     * @param Json $json
     * @param GetCleanedContents $getCleanedContents
     * @param Client|null $client
     * @param string $key
     * @param null $expire
     */
    public function __construct(
        CacheStorage $cacheStorage,
        Json $json,
        GetCleanedContents $getCleanedContents,
        Client $client = null,
        $key = 'flysystem',
        $expire = null
    ) {
        $this->client = $client ?: new Client();
        $this->key = $key;
        $this->expire = $expire;
        $this->cacheStorage = $cacheStorage;
        $this->json = $json;
        $this->getCleanedContents = $getCleanedContents;
    }

    /**
     * {@inheritdoc}
     */
    public function load(): void
    {
        if (($contents = $this->executeCommand('get', [$this->key])) !== null) {
            $this->setFromStorage($contents);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function save(): void
    {
        $contents = $this->getForStorage();
        $this->executeCommand('set', [$this->key, $contents]);

        if ($this->expire !== null) {
            $this->executeCommand('expire', [$this->key, $this->expire]);
        }
    }

    /**
     * Load from serialized cache data.
     *
     * @param string $json
     */
    private function setFromStorage(string $json): void
    {
        [$cache, $complete] = $this->json->unserialize($json);
        $this->cacheStorage->setCacheData($cache);
        $this->cacheStorage->setCompleteData($complete);
    }

    /**
     * Retrieve serialized cache data.
     *
     * @return string
     */
    private function getForStorage(): string
    {
        $cleaned = $this->getCleanedContents->execute($this->cacheStorage->getCacheData());

        return $this->json->serialize([$cleaned, $this->cacheStorage->getCompleteData()]);
    }

    /**
     * Execute a Predis command.
     *
     * @param string $name
     * @param array $arguments
     * @return string
     */
    private function executeCommand(string $name, array $arguments): string
    {
        $command = $this->client->createCommand($name, $arguments);

        return $this->client->executeCommand($command);
    }
}
