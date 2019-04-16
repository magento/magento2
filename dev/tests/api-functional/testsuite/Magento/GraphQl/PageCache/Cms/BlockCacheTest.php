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
        $responseHeaders = $this->graphQlQueryForHttpHeaders($query);
        preg_match('/X-Magento-Tags: (.*)/', $responseHeaders, $matches);
        $this->assertNotEmpty($matches[1]);
        $actualTags = explode(',', $matches[1]);
        $expectedTags = ["cms_b_{$blockIdentifier}", "cms_b_{$blockId}"];
        foreach ($expectedTags as $expectedTag) {
            $this->assertContains($expectedTag, $actualTags);
        }
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
        $responseMissHeaders = $this->graphQlQueryForHttpHeaders($query);
        $this->assertContains('X-Magento-Cache-Debug: MISS', $responseMissHeaders);

        //cache-debug should be a HIT on second request
        $responseHitHeaders = $this->graphQlQueryForHttpHeaders($query);
        $this->assertContains('X-Magento-Cache-Debug: HIT', $responseHitHeaders);

        //cached data should be correct
        $blockQueryData = $this->graphQlQuery($query);
        $blocks = $blockQueryData['cmsBlocks']['items'];
        $this->assertArrayNotHasKey('errors', $blockQueryData);
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
        $responseMissHeaders = $this->graphQlQueryForHttpHeaders($fixtureBlockQuery);
        $this->assertContains('X-Magento-Cache-Debug: MISS', $responseMissHeaders);
        $responseMissHeaders = $this->graphQlQueryForHttpHeaders($enabledBlockQuery);
        $this->assertContains('X-Magento-Cache-Debug: MISS', $responseMissHeaders);

        //cache-debug should be a HIT on second request
        $responseHitHeaders = $this->graphQlQueryForHttpHeaders($fixtureBlockQuery);
        $this->assertContains('X-Magento-Cache-Debug: HIT', $responseHitHeaders);
        $responseHitHeaders = $this->graphQlQueryForHttpHeaders($enabledBlockQuery);
        $this->assertContains('X-Magento-Cache-Debug: HIT', $responseHitHeaders);

        $newBlockContent = 'New block content!!!';
        $this->updateBlockContent($fixtureBlockIdentifier, $newBlockContent);

        //cache-debug should be a MISS after update the block
        $responseMissHeaders = $this->graphQlQueryForHttpHeaders($fixtureBlockQuery);
        $this->assertContains('X-Magento-Cache-Debug: MISS', $responseMissHeaders);
        $responseHitHeaders = $this->graphQlQueryForHttpHeaders($enabledBlockQuery);
        $this->assertContains('X-Magento-Cache-Debug: MISS', $responseHitHeaders);

        //updated block data should be correct
        $blockQueryData = $this->graphQlQuery($fixtureBlockQuery);
        $blocks = $blockQueryData['cmsBlocks']['items'];
        $this->assertArrayNotHasKey('errors', $blockQueryData);
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
