<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Config\Test\Unit;

use Magento\Framework\App\ObjectManager\ConfigWriterInterface;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Config\Data;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Test double that overrides filesystem-dependent methods for compiled config
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class TestableCompiledData extends Data
{
    /**
     * @var bool
     */
    private bool $compiledFileExists;

    /**
     * @var array|null
     */
    private ?array $compiledData;

    /**
     * @var string[]
     */
    private array $removedKeys = [];

    /**
     * @param ReaderInterface $reader
     * @param CacheInterface $cache
     * @param string $cacheId
     * @param SerializerInterface $serializer
     * @param bool $compiledFileExists
     * @param array|null $compiledData
     * @param ConfigWriterInterface|null $configWriter
     */
    public function __construct(
        ReaderInterface $reader,
        CacheInterface $cache,
        string $cacheId,
        SerializerInterface $serializer,
        bool $compiledFileExists,
        ?array $compiledData,
        ?ConfigWriterInterface $configWriter
    ) {
        $this->compiledFileExists = $compiledFileExists;
        $this->compiledData = $compiledData;
        parent::__construct($reader, $cache, $cacheId, $serializer, null, $configWriter);
    }

    /**
     * @inheritdoc
     */
    protected function isCompiledConfigAvailable(string $key): bool
    {
        return $this->compiledFileExists;
    }

    /**
     * @inheritdoc
     */
    protected function loadCompiledConfig(string $key): array
    {
        return $this->compiledData ?? [];
    }

    /**
     * @inheritdoc
     */
    protected function removeCompiledConfig(string $key): void
    {
        $this->removedKeys[] = $key;
    }

    /**
     * Get list of removed compiled config keys
     *
     * @return string[]
     */
    public function getRemovedCompiledKeys(): array
    {
        return $this->removedKeys;
    }
}
