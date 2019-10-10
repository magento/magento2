<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller\Cms;

use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Model\BlockRepository;
use Magento\Framework\App\Response\HttpInterface as HttpResponse;
use Magento\GraphQlCache\Controller\AbstractGraphqlCacheTest;

/**
 * Test caching works for CMS blocks
 *
 * @magentoAppArea graphql
 * @magentoCache full_page enabled
 * @magentoDbIsolation disabled
 */
class BlockCacheTest extends AbstractGraphqlCacheTest
{
    private function assertPageCacheMissWithTagsForCmsBlock(HttpResponse $response, BlockInterface $block): void
    {
        $this->assertEquals('MISS', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $this->assertCmsBlockCacheTags($response, $block);
    }

    private function assertPageCacheHitWithTagsForCmsBlock(HttpResponse $response, BlockInterface $block): void
    {
        $this->assertEquals('HIT', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $this->assertCmsBlockCacheTags($response, $block);
    }

    private function assertCmsBlockCacheTags(HttpResponse $response, BlockInterface $block): void
    {
        $expectedCacheTags  = ['cms_b', 'cms_b_' . $block->getId(), 'cms_b_' . $block->getIdentifier(), 'FPC'];
        $rawActualCacheTags = $response->getHeader('X-Magento-Tags')->getFieldValue();
        $actualCacheTags    = explode(',', $rawActualCacheTags);
        $this->assertEquals($expectedCacheTags, $actualCacheTags);
    }

    /**
     * @magentoDataFixture Magento/Cms/_files/block.php
     * @magentoDataFixture Magento/Cms/_files/blocks.php
     */
    public function testCmsBlocksRequestHasCorrectTags(): void
    {
        /** @var BlockRepository $blockRepository */
        $blockRepository = $this->objectManager->get(BlockRepository::class);

        $block1Identifier = 'fixture_block';
        $block1           = $blockRepository->getById($block1Identifier);
        $block2Identifier = 'enabled_block';
        $block2           = $blockRepository->getById($block2Identifier);

        $queryBlock1
            = <<<QUERY
 {
    cmsBlocks(identifiers: ["$block1Identifier"]) {
        items {
            title
    	    identifier
            content
        }
    }
}
QUERY;

        $queryBlock2
            = <<<QUERY
 {
    cmsBlocks(identifiers: ["$block2Identifier"]) {
        items {
            title
    	    identifier
            content
        }
    }
}
QUERY;

        // check to see that the first entity gets a MISS when called the first time
        $response = $this->dispatchGraphQlGETRequest(['query' => $queryBlock1]);
        $this->assertPageCacheMissWithTagsForCmsBlock($response, $block1);

        // check to see that the second entity gets a MISS when called the first time
        $response = $this->dispatchGraphQlGETRequest(['query' => $queryBlock2]);
        $this->assertPageCacheMissWithTagsForCmsBlock($response, $block2);

        // check to see that the first entity gets a HIT when called the second time
        $response = $this->dispatchGraphQlGETRequest(['query' => $queryBlock1]);
        $this->assertPageCacheHitWithTagsForCmsBlock($response, $block1);

        // check to see that the second entity gets a HIT when called the second time
        $response = $this->dispatchGraphQlGETRequest(['query' => $queryBlock2]);
        $this->assertPageCacheHitWithTagsForCmsBlock($response, $block2);

        $block1->setTitle('something else that causes invalidation');
        $blockRepository->save($block1);

        // check to see that the first entity gets a MISS and it was invalidated
        $response = $this->dispatchGraphQlGETRequest(['query' => $queryBlock1]);
        $this->assertPageCacheMissWithTagsForCmsBlock($response, $block1);

        // check to see that the first entity gets a HIT when called the second time
        $response = $this->dispatchGraphQlGETRequest(['query' => $queryBlock1]);
        $this->assertPageCacheHitWithTagsForCmsBlock($response, $block1);
    }
}
