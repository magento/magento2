<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Csp\Model\SubresourceIntegrity\StorageInterface;

/**
 * Class contains methods equivalent to repository design to manage SRI hashes.
 */
class SubresourceIntegrityRepository
{
    /**
     * @var array|null
     */
    private ?array $data = null;

    /**
     * @var string|null
     */
    private ?string $context;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var StorageInterface
     */
    private StorageInterface $storage;

    /**
     * @param CacheInterface $cache
     * @param SerializerInterface $serializer
     * @param SubresourceIntegrityFactory $integrityFactory
     * @param string|null $context
     * @param StorageInterface|null $storage
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        CacheInterface $cache,
        SerializerInterface $serializer,
        SubresourceIntegrityFactory $integrityFactory,
        ?string $context = null,
        ?StorageInterface $storage = null
    ) {
        $this->serializer = $serializer;
        $this->context = $context;

        $this->storage = $storage ?? ObjectManager::getInstance()->get(
            StorageInterface::class
        );
    }

    /**
     * Gets an Integrity object by path.
     *
     * @param string $path
     *
     * @return SubresourceIntegrity|null
     */
    public function getByPath(string $path): ?SubresourceIntegrity
    {
        return $this->getData()[$path] ?? null;
    }

    /**
     * Gets all available Integrity objects.
     *
     * @return SubresourceIntegrity[]
     */
    public function getAll(): array
    {
        return array_values($this->getData());
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

        $data[$integrity->getPath()] = $integrity;

        $this->data = $data;

        // Transform the data before saving.
        $transformedData = array_map(fn($integrity) => $integrity->getHash(), $this->data);

        return $this->storage->save(
            $this->serializer->serialize($transformedData),
            $this->context
        );
    }

    /**
     * Saves a bunch of Integrity objects.
     *
     * @param SubresourceIntegrity[] $bunch
     *
     * @return bool
     */
    public function saveBunch(array $bunch): bool
    {
        $data = $this->getData();

        foreach ($bunch as $integrity) {
            $data[$integrity->getPath()] = $integrity;
        }

        $this->data = $data;

        // Transform the data before saving.
        $transformedData = array_map(fn($integrity) => $integrity->getHash(), $this->data);

        return $this->storage->save(
            $this->serializer->serialize($transformedData),
            $this->context
        );
    }

    /**
     * Deletes all Integrity objects.
     *
     * @return bool
     */
    public function deleteAll(): bool
    {
        $this->data = null;

        return $this->storage->remove($this->context);
    }

    /**
     * Loads integrity data from a storage.
     *
     * @return array
     */
    private function getData(): array
    {
        if ($this->data === null) {
            $rawData = $this->storage->load($this->context);

            $this->data = $rawData ? $this->serializer->unserialize($rawData) : [];

            foreach ($this->data as $path => $hash) {
                $this->data[$path] = new SubresourceIntegrity(["path" => $path, "hash" => $hash]);
            }
        }

        return $this->data;
    }
}
