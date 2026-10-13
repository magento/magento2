<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cms\Block;

use Magento\Framework\Cache\Backend\RemoteSynchronizedCache;

/**
 * Verifies CMS block rendering through both tiers of the legacy Zend L1/L2 backend.
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 * @magentoCache block_html enabled
 */
class ZendCmsBlockL1L2Test extends AbstractCmsBlockL1L2TestCase
{
    /**
     * @magentoDataFixture Magento/Cms/_files/block.php
     */
    public function testL1MissLoadsCmsBlockFromL2AndRepopulatesL1(): void
    {
        $this->assertCmsBlockServedThroughBothTiers();
    }

    /**
     * @inheritDoc
     */
    protected function isExpectedBackend(object $backend): bool
    {
        return $backend instanceof RemoteSynchronizedCache;
    }

    /**
     * @inheritDoc
     */
    protected function getSkipMessage(): string
    {
        return 'Requires the legacy Zend L1/L2 cache backend.';
    }

    /**
     * @inheritDoc
     */
    protected function getLocalTier(object $backend)
    {
        return (new \ReflectionProperty($backend, 'local'))->getValue($backend);
    }

    /**
     * @inheritDoc
     */
    protected function getRemoteTier(object $backend)
    {
        return (new \ReflectionProperty($backend, 'remote'))->getValue($backend);
    }

    /**
     * @inheritDoc
     */
    protected function resolveTierCacheId(object $frontend, string $logicalCacheId): string
    {
        // Zend adapter uppercases identifiers before Zend_Cache_Core adds the prefix.
        return (string) $frontend->getLowLevelFrontend()->getOption('cache_id_prefix') . strtoupper($logicalCacheId);
    }
}
