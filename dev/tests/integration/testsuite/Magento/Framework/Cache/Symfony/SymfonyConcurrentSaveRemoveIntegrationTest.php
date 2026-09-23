<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Symfony;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Model\Block;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Cache\Backend\SymfonyL2Cache;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration test for concurrent save() and remove() consistency in SymfonyL2Cache
 * using a real Adobe Commerce scenario (CMS Block cache generation during block update/invalidation).
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 * @magentoCache block_html enabled
 */
class SymfonyConcurrentSaveRemoveIntegrationTest extends TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CacheInterface
     */
    private CacheInterface $cache;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->cache = $this->objectManager->get(CacheInterface::class);

        $backend = $this->cache->getFrontend()->getBackend();
        if (!$backend instanceof SymfonyL2Cache) {
            $this->markTestSkipped('Test requires SymfonyL2Cache backend.');
        }
    }

    /**
     * Simulates concurrent block rendering (save) and block update/clean (remove) on the same cache key.
     * Asserts that load() and test() maintain strict coherence and never produce a split-brain state.
     *
     * @magentoDataFixture Magento/Cms/_files/block.php
     * @return void
     */
    public function testConcurrentBlockRenderAndInvalidationMaintainsConsistency(): void
    {
        /** @var BlockRepositoryInterface $blockRepository */
        $blockRepository = $this->objectManager->get(BlockRepositoryInterface::class);
        $block = $blockRepository->getById('fixture_block');
        $cacheKey = Block::CACHE_TAG . '_' . $block->getId();
        $tags = [Block::CACHE_TAG, Block::CACHE_TAG . '_' . $block->getId()];

        $backend = $this->cache->getFrontend()->getBackend();

        // Warm initial cache entry
        $this->cache->save('<h1>Initial Rendered Block</h1>', $cacheKey, $tags, 3600);
        $this->assertSame('<h1>Initial Rendered Block</h1>', $this->cache->load($cacheKey));

        if (!function_exists('pcntl_fork')) {
            // Fallback for environments without pcntl: execute sequential interleaved operations
            $this->cache->remove($cacheKey);
            $this->cache->save('<h1>Regenerated Block</h1>', $cacheKey, $tags, 3600);
            $this->assertSame('<h1>Regenerated Block</h1>', $this->cache->load($cacheKey));
            $this->assertNotFalse($backend->test($cacheKey));
            return;
        }

        $iterations = 10;
        for ($i = 0; $i < $iterations; $i++) {
            $renderedHtml = '<h1>Raced Block Content #' . $i . '</h1>';

            $pidSave = pcntl_fork();
            if ($pidSave === -1) {
                $this->fail('pcntl_fork failed for save process');
            }

            if ($pidSave === 0) {
                // Child A: Simulate concurrent block render cache save
                $res = $this->cache->save($renderedHtml, $cacheKey, $tags, 3600);
                posix_kill(getmypid(), SIGKILL);
            }

            $pidRemove = pcntl_fork();
            if ($pidRemove === -1) {
                $this->fail('pcntl_fork failed for remove process');
            }

            if ($pidRemove === 0) {
                // Child B: Simulate concurrent admin block save / cache invalidation
                $res = $this->cache->remove($cacheKey);
                posix_kill(getmypid(), SIGKILL);
            }

            $statusA = 0;
            $statusB = 0;
            pcntl_waitpid($pidSave, $statusA);
            pcntl_waitpid($pidRemove, $statusB);

            $loadResult = $this->cache->load($cacheKey);
            $testResult = $backend->test($cacheKey);

            if ($loadResult === false) {
                $this->assertFalse(
                    (bool)$testResult,
                    "Inconsistent state on iteration {$i}: load() returned false but test() reported present."
                );
            } else {
                $this->assertTrue(
                    (bool)$testResult,
                    "Inconsistent state on iteration {$i}: load() returned data but test() reported absent."
                );
            }
        }
    }
}
