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
 * Test the caching works properly for CMS Blocks
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
        // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheMissAndReturnResponse($query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);
        // Verify we obtain a cache HIT the second time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheHitAndReturnResponse($query, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        //cached data should be correct
        $this->assertNotEmpty($response['body']);
        $this->assertArrayNotHasKey('errors', $response['body']);
        $blocks = $response['body']['cmsBlocks']['items'];
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
        $cacheId = $fixtureBlock['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheMissAndReturnResponse($fixtureBlockQuery, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);
        // Verify we obtain a cache HIT the second time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheHitAndReturnResponse($fixtureBlockQuery, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        $enabledBlock = $this->graphQlQueryWithResponseHeaders($enabledBlockQuery);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $enabledBlock['headers']);
        $cacheId = $enabledBlock['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheMissAndReturnResponse($enabledBlockQuery, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);
        // Verify we obtain a cache HIT the second time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheHitAndReturnResponse($enabledBlockQuery, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        $newBlockContent = 'New block content!!!';
        $this->updateBlockContent($fixtureBlockIdentifier, $newBlockContent);

        //cache-debug should be a MISS after update the block on fixture block query
        $fixtureBlock = $this->graphQlQueryWithResponseHeaders($fixtureBlockQuery);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $fixtureBlock['headers']);
        $cacheId = $fixtureBlock['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache MISS the first time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheMissAndReturnResponse($fixtureBlockQuery, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        //cache-debug should be a HIT after update the block on enabled block query
        $enabledBlock = $this->graphQlQueryWithResponseHeaders($enabledBlockQuery);
        $this->assertArrayHasKey(CacheIdCalculator::CACHE_ID_HEADER, $enabledBlock['headers']);
        $cacheId = $enabledBlock['headers'][CacheIdCalculator::CACHE_ID_HEADER];
        // Verify we obtain a cache HIT the second time we search the cache using this X-Magento-Cache-Id
        $this->assertCacheHitAndReturnResponse($enabledBlockQuery, [CacheIdCalculator::CACHE_ID_HEADER => $cacheId]);

        //updated block data should be correct
        $this->assertNotEmpty($fixtureBlock['body']);
        $blocks = $fixtureBlock['body']['cmsBlocks']['items'];
        $this->assertArrayNotHasKey('errors', $fixtureBlock['body']);
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
