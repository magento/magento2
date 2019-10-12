<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\PageCache\Cms;

use Magento\Cms\Model\Block;
use Magento\Cms\Model\BlockRepository;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\GraphQlAbstract;

/**
 * Test the caching works properly for CMS Blocks
 */
class BlockCacheTest extends GraphQlAbstract
{
    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->markTestSkipped(
            'This test will stay skipped until DEVOPS-4924 is resolved'
        );
    }

    /**
     * Test that X-Magento-Tags are correct
     *
     * @magentoApiDataFixture Magento/Cms/_files/block.php
     */
    public function testCacheTagsHaveExpectedValue()
    {
        $blockIdentifier = 'fixture_block';
        $blockRepository = Bootstrap::getObjectManager()->get(BlockRepository::class);
        $block = $blockRepository->getById($blockIdentifier);
        $blockId = $block->getId();
        $query = $this->getBlockQuery([$blockIdentifier]);

        //cache-debug should be a MISS on first request
        $response = $this->graphQlQueryWithResponseHeaders($query);

        $this->assertArrayHasKey('X-Magento-Tags', $response['headers']);
        $actualTags = explode(',', $response['headers']['X-Magento-Tags']);
        $expectedTags = ["cms_b", "cms_b_{$blockId}", "cms_b_{$blockIdentifier}", "FPC"];
        $this->assertEquals($expectedTags, $actualTags);
    }

    /**
     * Test the second request for the same block will return a cached result
     *
     * @magentoApiDataFixture Magento/Cms/_files/block.php
     */
    public function testCacheIsUsedOnSecondRequest()
    {
        $blockIdentifier = 'fixture_block';
        $query = $this->getBlockQuery([$blockIdentifier]);

        //cache-debug should be a MISS on first request
        $responseMiss = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey('X-Magento-Cache-Debug', $responseMiss['headers']);
        $this->assertEquals('MISS', $responseMiss['headers']['X-Magento-Cache-Debug']);

        //cache-debug should be a HIT on second request
        $responseHit = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey('X-Magento-Cache-Debug', $responseHit['headers']);
        $this->assertEquals('HIT', $responseHit['headers']['X-Magento-Cache-Debug']);
        //cached data should be correct
        $this->assertNotEmpty($responseHit['body']);
        $this->assertArrayNotHasKey('errors', $responseHit['body']);
        $blocks = $responseHit['body']['cmsBlocks']['items'];
        $this->assertEquals($blockIdentifier, $blocks[0]['identifier']);
        $this->assertEquals('CMS Block Title', $blocks[0]['title']);
    }

    /**
     * Test that cache is invalidated when block is updated
     *
     * @magentoApiDataFixture Magento/Cms/_files/blocks.php
     * @magentoApiDataFixture Magento/Cms/_files/block.php
     */
    public function testCacheIsInvalidatedOnBlockUpdate()
    {
        $fixtureBlockIdentifier = 'fixture_block';
        $enabledBlockIdentifier = 'enabled_block';
        $fixtureBlockQuery = $this->getBlockQuery([$fixtureBlockIdentifier]);
        $enabledBlockQuery = $this->getBlockQuery([$enabledBlockIdentifier]);

        //cache-debug should be a MISS on first request
        $fixtureBlockMiss = $this->graphQlQueryWithResponseHeaders($fixtureBlockQuery);
        $this->assertEquals('MISS', $fixtureBlockMiss['headers']['X-Magento-Cache-Debug']);
        $enabledBlockMiss = $this->graphQlQueryWithResponseHeaders($enabledBlockQuery);
        $this->assertEquals('MISS', $enabledBlockMiss['headers']['X-Magento-Cache-Debug']);

        //cache-debug should be a HIT on second request
        $fixtureBlockHit = $this->graphQlQueryWithResponseHeaders($fixtureBlockQuery);
        $this->assertEquals('HIT', $fixtureBlockHit['headers']['X-Magento-Cache-Debug']);
        $enabledBlockHit = $this->graphQlQueryWithResponseHeaders($enabledBlockQuery);
        $this->assertEquals('HIT', $enabledBlockHit['headers']['X-Magento-Cache-Debug']);

        $newBlockContent = 'New block content!!!';
        $this->updateBlockContent($fixtureBlockIdentifier, $newBlockContent);

        //cache-debug should be a MISS after update the block
        $fixtureBlockMiss = $this->graphQlQueryWithResponseHeaders($fixtureBlockQuery);
        $this->assertEquals('MISS', $fixtureBlockMiss['headers']['X-Magento-Cache-Debug']);
        $enabledBlockHit = $this->graphQlQueryWithResponseHeaders($enabledBlockQuery);
        $this->assertEquals('HIT', $enabledBlockHit['headers']['X-Magento-Cache-Debug']);
        //updated block data should be correct
        $this->assertNotEmpty($fixtureBlockMiss['body']);
        $blocks = $fixtureBlockMiss['body']['cmsBlocks']['items'];
        $this->assertArrayNotHasKey('errors', $fixtureBlockMiss['body']);
        $this->assertEquals($fixtureBlockIdentifier, $blocks[0]['identifier']);
        $this->assertEquals('CMS Block Title', $blocks[0]['title']);
        $this->assertEquals($newBlockContent, $blocks[0]['content']);
    }

    /**
     * Update the content of a CMS block
     *
     * @param $identifier
     * @param $newContent
     * @return Block
     */
    private function updateBlockContent($identifier, $newContent): Block
    {
        $blockRepository = Bootstrap::getObjectManager()->get(BlockRepository::class);
        $block = $blockRepository->getById($identifier);
        $block->setContent($newContent);
        $blockRepository->save($block);

        return $block;
    }

    /**
     * Get cmsBlocks query
     *
     * @param array $identifiers
     * @return string
     */
    private function getBlockQuery(array $identifiers): string
    {
        $identifiersString = implode(',', $identifiers);
        $query = <<<QUERY
    { 
        cmsBlocks(identifiers: ["$identifiersString"]) {
            items {
                title
                identifier
                content
            }
        }
    }
QUERY;
        return $query;
    }
}
