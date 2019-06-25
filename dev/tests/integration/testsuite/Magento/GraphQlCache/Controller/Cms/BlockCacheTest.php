<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQlCache\Controller\Cms;

use Magento\Cms\Model\BlockRepository;
use Magento\GraphQl\Controller\GraphQl;
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
    /**
     * @var GraphQl
     */
    private $graphqlController;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->graphqlController = $this->objectManager->get(\Magento\GraphQl\Controller\GraphQl::class);
    }

    /**
     * Test that the correct cache tags get added to request for cmsBlocks
     *
     * @magentoDataFixture Magento/Cms/_files/block.php
     * @magentoDataFixture Magento/Cms/_files/blocks.php
     */
    public function testCmsBlocksRequestHasCorrectTags(): void
    {
        /** @var BlockRepository $blockRepository */
        $blockRepository = $this->objectManager->get(BlockRepository::class);

        $block1Identifier = 'fixture_block';
        $block1 = $blockRepository->getById($block1Identifier);
        $block2Identifier = 'enabled_block';
        $block2 = $blockRepository->getById($block2Identifier);

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
        $request = $this->prepareRequest($queryBlock1);
        $response = $this->graphqlController->dispatch($request);
        $this->assertEquals('MISS', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $expectedCacheTags = ['cms_b', 'cms_b_' . $block1->getId(), 'cms_b_' . $block1->getIdentifier(), 'FPC'];
        $rawActualCacheTags = $response->getHeader('X-Magento-Tags')->getFieldValue();
        $actualCacheTags = explode(',', $rawActualCacheTags);
        $this->assertEquals($expectedCacheTags, $actualCacheTags);

        // check to see that the second entity gets a miss when called the first time
        $request = $this->prepareRequest($queryBlock2);
        $response = $this->graphqlController->dispatch($request);
        $this->assertEquals('MISS', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $expectedCacheTags = ['cms_b', 'cms_b_' . $block2->getId(), 'cms_b_' . $block2->getIdentifier(), 'FPC'];
        $rawActualCacheTags = $response->getHeader('X-Magento-Tags')->getFieldValue();
        $actualCacheTags = explode(',', $rawActualCacheTags);
        $this->assertEquals($expectedCacheTags, $actualCacheTags);

        // check to see that the first entity gets a HIT when called the second time
        $request = $this->prepareRequest($queryBlock1);
        $response = $this->graphqlController->dispatch($request);
        $this->assertEquals('HIT', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $expectedCacheTags = ['cms_b', 'cms_b_' . $block1->getId(), 'cms_b_' . $block1->getIdentifier(), 'FPC'];
        $rawActualCacheTags = $response->getHeader('X-Magento-Tags')->getFieldValue();
        $actualCacheTags = explode(',', $rawActualCacheTags);
        $this->assertEquals($expectedCacheTags, $actualCacheTags);

        // check to see that the second entity gets a HIT when called the second time
        $request = $this->prepareRequest($queryBlock2);
        $response = $this->graphqlController->dispatch($request);
        $this->assertEquals('HIT', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $expectedCacheTags = ['cms_b', 'cms_b_' . $block2->getId(), 'cms_b_' . $block2->getIdentifier(), 'FPC'];
        $rawActualCacheTags = $response->getHeader('X-Magento-Tags')->getFieldValue();
        $actualCacheTags = explode(',', $rawActualCacheTags);
        $this->assertEquals($expectedCacheTags, $actualCacheTags);

        $block1->setTitle('something else that causes invalidation');
        $blockRepository->save($block1);

        // check to see that the first entity gets a MISS and it was invalidated
        $request = $this->prepareRequest($queryBlock1);
        $response = $this->graphqlController->dispatch($request);
        $this->assertEquals('MISS', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $expectedCacheTags = ['cms_b', 'cms_b_' . $block1->getId(), 'cms_b_' . $block1->getIdentifier(), 'FPC'];
        $rawActualCacheTags = $response->getHeader('X-Magento-Tags')->getFieldValue();
        $actualCacheTags = explode(',', $rawActualCacheTags);
        $this->assertEquals($expectedCacheTags, $actualCacheTags);

        // check to see that the first entity gets a HIT when called the second time
        $request = $this->prepareRequest($queryBlock1);
        $response = $this->graphqlController->dispatch($request);
        $this->assertEquals('HIT', $response->getHeader('X-Magento-Cache-Debug')->getFieldValue());
        $expectedCacheTags = ['cms_b', 'cms_b_' . $block1->getId(), 'cms_b_' . $block1->getIdentifier(), 'FPC'];
        $rawActualCacheTags = $response->getHeader('X-Magento-Tags')->getFieldValue();
        $actualCacheTags = explode(',', $rawActualCacheTags);
        $this->assertEquals($expectedCacheTags, $actualCacheTags);
    }
}
