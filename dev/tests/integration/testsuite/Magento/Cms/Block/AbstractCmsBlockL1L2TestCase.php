<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cms\Block;

use Magento\Cms\Model\BlockRepository;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Cache\Type\Block as BlockCacheType;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Shared flow: render a CMS block and assert it is served from L1, backed by L2, and that a cold L1
 * self-heals from L2. Concrete profiles supply the backend check and tier access.
 */
abstract class AbstractCmsBlockL1L2TestCase extends TestCase
{
    /**
     * Whether the runtime backend is the two-tier backend this profile expects.
     *
     * @param object $backend
     * @return bool
     */
    abstract protected function isExpectedBackend(object $backend): bool;

    /**
     * Skip message when the expected backend is not active.
     *
     * @return string
     */
    abstract protected function getSkipMessage(): string;

    /**
     * The local (L1) tier of the two-tier backend.
     *
     * @param object $backend
     * @return mixed
     */
    abstract protected function getLocalTier(object $backend);

    /**
     * The remote (L2) tier of the two-tier backend.
     *
     * @param object $backend
     * @return mixed
     */
    abstract protected function getRemoteTier(object $backend);

    /**
     * Translate the logical cache key into the id the raw tiers store it under.
     *
     * @param object $frontend
     * @param string $logicalCacheId
     * @return string
     */
    abstract protected function resolveTierCacheId(object $frontend, string $logicalCacheId): string;

    /**
     * Render a CMS block, then assert both tiers hold it and a cold L1 self-heals from L2.
     *
     * @return void
     */
    protected function assertCmsBlockServedThroughBothTiers(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $frontend = $objectManager->get(Pool::class)->get(BlockCacheType::TYPE_IDENTIFIER);
        $backend = $frontend->getBackend();

        if (!$this->isExpectedBackend($backend)) {
            $this->markTestSkipped($this->getSkipMessage());
        }

        $cmsBlock = $objectManager->get(BlockRepository::class)->getById('fixture_block');
        $block = $objectManager->get(LayoutInterface::class)->createBlock(Block::class);
        $block->setBlockId((string) $cmsBlock->getId())
            ->setCacheLifetime(3600)
            ->setCacheKey('integration_cms_block_l1l2_' . $cmsBlock->getId());
        $html = (string) $block->toHtml();
        $logicalCacheId = $block->getCacheKey();
        $tierCacheId = $this->resolveTierCacheId($frontend, $logicalCacheId);

        $local = $this->getLocalTier($backend);
        $remote = $this->getRemoteTier($backend);

        try {
            $this->assertSame($html, $local->load($tierCacheId));
            $this->assertNotFalse($remote->load($tierCacheId));
            $local->remove($tierCacheId);
            $this->assertFalse($local->load($tierCacheId));
            $this->assertSame($html, $frontend->load($logicalCacheId));
            $this->assertNotFalse($local->load($tierCacheId));
        } finally {
            $objectManager->get(TypeListInterface::class)->cleanType(BlockCacheType::TYPE_IDENTIFIER);
        }
    }
}
