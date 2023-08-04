<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\PageCache\Cms;

use Magento\Cms\Model\Block;
use Magento\Cms\Model\BlockRepository;
use Magento\GraphQl\PageCache\GraphQLPageCacheAbstract;
use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test the cache works properly for CMS Blocks
 */
class BlockCacheTest extends GraphQLPageCacheAbstract
{
    /**
     * Test the second request for the same block will return a cached result
     *
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Cms/_files/block.php
     */
    public function testCacheIsUsedOnSecondRequest()
    {
        $blockIdentifier = 'fixture_block';
        $query = $this->getBlockQuery([$blockIdentifier]);

        //cache-debug should be a MISS on first request and HIT on the second request
        $response = $this->graphQlQueryWithResponseHeaders($query);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $response['headers']);
        $cacheId = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS the first time
        $this->assertCacheMissAndReturnResponse($query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);
        // Verify we obtain a cache HIT the second time
        $responseHit = $this->assertCacheHitAndReturnResponse(
            $query,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]
        );

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
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @magentoApiDataFixture Magento/Cms/_files/blocks.php
     * @magentoApiDataFixture Magento/Cms/_files/block.php
     */
    public function testCacheIsInvalidatedOnBlockUpdate()
    {
        $fixtureBlockIdentifier = 'fixture_block';
        $enabledBlockIdentifier = 'enabled_block';
        $fixtureBlockQuery = $this->getBlockQuery([$fixtureBlockIdentifier]);
        $enabledBlockQuery = $this->getBlockQuery([$enabledBlockIdentifier]);

        //cache-debug should be a MISS on first request and HIT on second request
        $fixtureBlock = $this->graphQlQueryWithResponseHeaders($fixtureBlockQuery);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $fixtureBlock['headers']);
        $cacheIdOfFixtureBlock = $fixtureBlock['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS the first time
        $this->assertCacheMissAndReturnResponse(
            $fixtureBlockQuery,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheIdOfFixtureBlock]
        );

        $enabledBlock = $this->graphQlQueryWithResponseHeaders($enabledBlockQuery);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $enabledBlock['headers']);
        $cacheIdOfEnabledBlock = $enabledBlock['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS the first time
        $this->assertCacheMissAndReturnResponse(
            $enabledBlockQuery,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheIdOfEnabledBlock]
        );

        //cache should be a HIT on second request
        // Verify we obtain a cache HIT the second time
        $this->assertCacheHitAndReturnResponse(
            $fixtureBlockQuery,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheIdOfFixtureBlock]
        );
        // Verify we obtain a cache HIT the second time
        $this->assertCacheHitAndReturnResponse(
            $enabledBlockQuery,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheIdOfEnabledBlock]
        );

        //updating content on fixture block
        $newBlockContent = 'New block content!!!';
        $this->updateBlockContent($fixtureBlockIdentifier, $newBlockContent);

        // Verify we obtain a cache MISS on the fixture block query
        // after the content update on the fixture block
        $this->assertCacheMissAndReturnResponse(
            $fixtureBlockQuery,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheIdOfFixtureBlock]
        );

        $fixtureBlockHitResponse = $this->assertCacheHitAndReturnResponse(
            $fixtureBlockQuery,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheIdOfFixtureBlock]
        );

        //Verify we obtain a cache HIT on the enabled block query after the fixture block is updated
        $this->assertCacheHitAndReturnResponse(
            $enabledBlockQuery,
            [CacheIdCalculator::CACHE_ID_HEADER => $cacheIdOfEnabledBlock]
        );

        //updated block data should be correct on fixture block
        $this->assertNotEmpty($fixtureBlockHitResponse['body']);
        $blocks = $fixtureBlockHitResponse['body']['cmsBlocks']['items'];
        $this->assertArrayNotHasKey('errors', $fixtureBlockHitResponse['body']);
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
