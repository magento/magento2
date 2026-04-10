<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Config\Test\Unit\Data;

use Magento\Framework\App\ObjectManager\ConfigWriterInterface;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\Config\Data\Scoped;
use Magento\Framework\Config\ReaderInterface;
use Magento\Framework\Config\ScopeInterface;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Test double that overrides filesystem-dependent methods for compiled scoped config
 *
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class TestableCompiledScoped extends Scoped
{
    /**
     * @var array<string, array>
     */
    private array $compiledScopes;

    /**
     * @param ReaderInterface $reader
     * @param ScopeInterface $configScope
     * @param CacheInterface $cache
     * @param string $cacheId
     * @param SerializerInterface $serializer
     * @param array $compiledScopes
     * @param ConfigWriterInterface|null $configWriter
     */
    public function __construct(
        ReaderInterface $reader,
        ScopeInterface $configScope,
        CacheInterface $cache,
        string $cacheId,
        SerializerInterface $serializer,
        array $compiledScopes,
        ?ConfigWriterInterface $configWriter
    ) {
        $this->compiledScopes = $compiledScopes;
        parent::__construct($reader, $configScope, $cache, $cacheId, $serializer, $configWriter);
    }

    /**
     * @inheritdoc
     */
    protected function isCompiledConfigAvailable(string $key): bool
    {
        return isset($this->compiledScopes[$key]);
    }

    /**
     * @inheritdoc
     */
    protected function loadCompiledConfig(string $key): array
    {
        return $this->compiledScopes[$key] ?? [];
    }
}
