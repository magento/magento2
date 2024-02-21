<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Class contains methods equivalent to repository design to manage SRI hashes in cache.
 */
class SubresourceIntegrityRepository
{
    /**
     * Cache prefix.
     *
     * @var string
     */
    private const CACHE_PREFIX = 'INTEGRITY';

    /**
     * @var array|null
     */
    private ?array $data = null;

    /**
     * @var CacheInterface
     */
    private CacheInterface $cache;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var SubresourceIntegrityFactory
     */
    private SubresourceIntegrityFactory $integrityFactory;

    /**
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     * @param SubresourceIntegrityFactory $integrityFactory
     */
    public function __construct(
        CacheInterface $cache,
        SerializerInterface $serializer,
        SubresourceIntegrityFactory $integrityFactory
    ) {
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->integrityFactory = $integrityFactory;
    }

    /**
     * Gets an Integrity object by URL.
     *
     * @param string $url
     *
     * @return SubresourceIntegrity|null
     */
    public function getByUrl(string $url): ?SubresourceIntegrity
    {
        $data = $this->getData();

        if (isset($data[$url])) {
            return $this->integrityFactory->create(
                ["data" => $data[$url]]
            );
        }

        return null;
    }

    /**
     * Gets all available Integrity objects.
     *
     * @return SubresourceIntegrity[]
     */
    public function getAll(): array
    {
        $result = [];

        foreach ($this->getData() as $integrity) {
            $result[] = $this->integrityFactory->create(
                [
                    "data" => $integrity
                ]
            );
        }

        return $result;
    }

    /**
     * Saves Integrity object.
     *
     * @param SubresourceIntegrity $integrity
     *
     * @return bool
     */
    public function save(SubresourceIntegrity $integrity): bool
    {
        $data = $this->getData();

        $data[$integrity->getUrl()] = [
            "url" => $integrity->getUrl(),
            "hash" => $integrity->getHash()
        ];

        $this->data = $data;

        return $this->cache->save(
            $this->serializer->serialize($this->data),
            self::CACHE_PREFIX,
            [self::CACHE_PREFIX]
        );
    }

    /**
     * Loads integrity data from a storage.
     *
     * @return array
     */
    private function getData(): array
    {
        if ($this->data === null) {
            $cache = $this->cache->load(self::CACHE_PREFIX);

            $this->data = $cache ? $this->serializer->unserialize($cache) : [];
        }

        return $this->data;
    }
}
