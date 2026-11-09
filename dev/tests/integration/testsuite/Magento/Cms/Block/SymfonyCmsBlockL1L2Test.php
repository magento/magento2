<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cms\Block;

use Magento\Framework\Cache\Backend\SymfonyL2Cache;

/**
 * Verifies CMS block rendering through both tiers of the Symfony L1/L2 backend.
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 * @magentoCache block_html enabled
 */
class SymfonyCmsBlockL1L2Test extends AbstractCmsBlockL1L2TestCase
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
        return $backend instanceof SymfonyL2Cache;
    }

    /**
     * @inheritDoc
     */
    protected function getSkipMessage(): string
    {
        return 'Requires the Symfony L1/L2 cache backend.';
    }

    /**
     * @inheritDoc
     */
    protected function getLocalTier(object $backend)
    {
        return $backend->getLocal();
    }

    /**
     * @inheritDoc
     */
    protected function getRemoteTier(object $backend)
    {
        return $backend->getRemote();
    }

    /**
     * @inheritDoc
     */
    protected function resolveTierCacheId(object $frontend, string $logicalCacheId): string
    {
        // Symfony tiers store entries under the logical id (prefixing is handled inside the adapter).
        return $logicalCacheId;
    }
}
