<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Cms\Block;

use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Model\BlockRepository;
use Magento\Framework\App\Cache\Type\Block as BlockCacheType;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Verifies CMS block rendering through the real frontend block and block_html cache.
 *
 * @magentoAppArea frontend
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 * @magentoCache block_html enabled
 */
class FrontendCacheTest extends TestCase
{
    /**
     * @magentoDataFixture Magento/Cms/_files/block.php
     */
    public function testRenderedBlockIsCachedAndRegeneratedAfterCacheClean(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $repository = $objectManager->get(BlockRepository::class);
        $layout = $objectManager->get(LayoutInterface::class);
        $block = $repository->getById('fixture_block');
        $originalTitle = $block->getTitle();
        $originalContent = $block->getContent();

        try {
            $first = $this->renderBlock($layout, $block);
            $this->assertStringContainsString('<h1>Fixture Block Title</h1>', $first);

            $second = $this->renderBlock($layout, $block);
            $this->assertSame($first, $second, 'The second render must use block_html cache.');

            $updatedContent = '<p>Updated CMS block content ' . uniqid() . '</p>';
            $block->setTitle($originalTitle . ' Updated')->setContent($updatedContent);
            $repository->save($block);

            // The deprecated Cms\Block\Block does not automatically invalidate block_html on save.
            $objectManager->get(TypeListInterface::class)->cleanType(BlockCacheType::TYPE_IDENTIFIER);

            $updated = $this->renderBlock($layout, $block);
            $this->assertStringContainsString($updatedContent, $updated);
            $this->assertStringNotContainsString($originalContent, $updated);
        } finally {
            $block->setTitle($originalTitle)->setContent($originalContent);
            $repository->save($block);
        }
    }

    private function renderBlock(LayoutInterface $layout, BlockInterface $cmsBlock): string
    {
        $block = $layout->createBlock(Block::class);
        $block->setBlockId((string) $cmsBlock->getId());
        $block->setCacheLifetime(3600);
        $block->setCacheKey('integration_cms_block_' . $cmsBlock->getId());

        return (string) $block->toHtml();
    }
}
