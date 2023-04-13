<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CmsGraphQl\Model\Resolver;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Model\Block;
use Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\Type as GraphQlCache;
use Magento\GraphQlCache\Model\CacheId\CacheIdCalculator;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\Widget\Model\Template\FilterEmulate;

class BlockTest extends GraphQlAbstract
{
    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * @var GraphQlCache
     */
    private $graphqlCache;

    /**
     * @var FilterEmulate
     */
    private $widgetFilter;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    protected function setUp(): void
    {
        $objectManager = ObjectManager::getInstance();
        $this->blockRepository = $objectManager->get(BlockRepositoryInterface::class);
        $this->graphqlCache = $objectManager->get(GraphQlCache::class);
        $this->widgetFilter = $objectManager->get(FilterEmulate::class);
        $this->storeManager = $objectManager->get(StoreManagerInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Cms/_files/blocks.php
     */
    public function testCmsSingleBlockResolverCacheAndInvalidationAsGuest()
    {
        $block = $this->blockRepository->getById('enabled_block');

        $query = $this->getQuery([
            $block->getIdentifier(),
        ]);

        $response = $this->graphQlQueryWithResponseHeaders($query);

        $cacheIdentityString = $this->getResolverCacheKeyFromResponseAndBlocks($response, [$block]);

        $cacheEntry = $this->graphqlCache->load($cacheIdentityString);
        $cacheEntryDecoded = json_decode($cacheEntry, true);

        $this->assertEqualsCanonicalizing(
            $this->generateExpectedDataFromBlocks([$block]),
            $cacheEntryDecoded
        );

        $this->assertTagsByCacheIdentityAndBlocks(
            $cacheIdentityString,
            [$block]
        );

        // assert that cache is invalidated after block content change
        $block->setContent('New Content');
        $this->blockRepository->save($block);

        $this->assertFalse(
            $this->graphqlCache->test($cacheIdentityString),
            'Cache entry should be invalidated after block content change'
        );
    }

    /**
     * @magentoDataFixture Magento/Cms/_files/block.php
     * @magentoDataFixture Magento/Cms/_files/blocks.php
     */
    public function testCmsMultipleBlockResolverCacheAndInvalidationAsGuest()
    {
        $block1 = $this->blockRepository->getById('enabled_block');
        $block2 = $this->blockRepository->getById('fixture_block');

        $query = $this->getQuery([
            $block1->getIdentifier(),
            $block2->getIdentifier(),
        ]);

        $response = $this->graphQlQueryWithResponseHeaders($query);

        $cacheIdentityString = $this->getResolverCacheKeyFromResponseAndBlocks($response, [
            $block1,
            $block2,
        ]);

        $cacheEntry = $this->graphqlCache->load($cacheIdentityString);
        $cacheEntryDecoded = json_decode($cacheEntry, true);

        $this->assertEqualsCanonicalizing(
            $this->generateExpectedDataFromBlocks([$block1, $block2]),
            $cacheEntryDecoded
        );

        $this->assertTagsByCacheIdentityAndBlocks(
            $cacheIdentityString,
            [$block1, $block2]
        );

        // assert that cache is invalidated after a single block content change
        $block2->setContent('New Content');
        $this->blockRepository->save($block2);

        $this->assertFalse(
            $this->graphqlCache->test($cacheIdentityString),
            'Cache entry should be invalidated after block content change'
        );
    }

    /**
     * @magentoDataFixture Magento/Cms/_files/block.php
     * @magentoDataFixture Magento/Store/_files/second_store.php
     */
    public function testCmsBlockResolverCacheIsInvalidatedAfterChangingItsStoreView()
    {
        /** @var Block $block */
        $block = $this->blockRepository->getById('fixture_block');

        $query = $this->getQuery([
            $block->getIdentifier(),
        ]);

        $response = $this->graphQlQueryWithResponseHeaders($query);

        $cacheIdentityString = $this->getResolverCacheKeyFromResponseAndBlocks($response, [$block]);

        $cacheEntry = $this->graphqlCache->load($cacheIdentityString);
        $cacheEntryDecoded = json_decode($cacheEntry, true);

        $this->assertEqualsCanonicalizing(
            $this->generateExpectedDataFromBlocks([$block]),
            $cacheEntryDecoded
        );

        $this->assertTagsByCacheIdentityAndBlocks(
            $cacheIdentityString,
            [$block]
        );

        // assert that cache is invalidated after changing block's store view
        $secondStoreViewId = $this->storeManager->getStore('fixture_second_store')->getId();
        $block->setStoreId($secondStoreViewId);
        $this->blockRepository->save($block);

        $this->assertFalse(
            $this->graphqlCache->test($cacheIdentityString),
            'Cache entry should be invalidated after changing block\'s store view'
        );
    }

    private function getQuery(array $identifiers): string
    {
        $identifiersStr = $this->getQuotedBlockIdentifiersListAsString($identifiers);

        return <<<QUERY
{
  cmsBlocks(identifiers: [$identifiersStr]) {
    items {
        title
        content
        identifier
    }
  }
}
QUERY;
    }

    /**
     * @param string[] $identifiers
     * @return string
     */
    private function getQuotedBlockIdentifiersListAsString(array $identifiers): string
    {
        return implode(',', array_map(function (string $identifier) {
            return "\"$identifier\"";
        }, $identifiers));
    }

    /**
     * @param BlockInterface[] $blocks
     * @return array
     */
    private function generateExpectedDataFromBlocks(array $blocks): array
    {
        $expectedBlockData = [];

        foreach ($blocks as $block) {
            $expectedBlockData[$block->getIdentifier()] = [
                'block_id' => $block->getId(),
                'identifier' => $block->getIdentifier(),
                'title' => $block->getTitle(),
                'content' => $this->widgetFilter->filterDirective($block->getContent()),
            ];
        }

        return [
            'items' => $expectedBlockData,
        ];
    }

    /**
     * @param string $cacheIdentityString
     * @param BlockInterface[] $blocks
     * @return void
     * @throws \Zend_Cache_Exception
     */
    private function assertTagsByCacheIdentityAndBlocks(string $cacheIdentityString, array $blocks): void
    {
        $lowLevelFrontendCache = $this->graphqlCache->getLowLevelFrontend();
        $cacheIdPrefix = $lowLevelFrontendCache->getOption('cache_id_prefix');
        $metadatas = $lowLevelFrontendCache->getMetadatas($cacheIdentityString);
        $tags = $metadatas['tags'];

        $expectedTags = [
            $cacheIdPrefix . strtoupper(Block::CACHE_TAG),
            $cacheIdPrefix . strtoupper(GraphQlCache::CACHE_TAG),
            $cacheIdPrefix . 'MAGE',
        ];

        foreach ($blocks as $block) {
            $expectedTags[] = $cacheIdPrefix . strtoupper(Block::CACHE_TAG) . '_' . $block->getId();
            $expectedTags[] = $cacheIdPrefix . strtoupper(Block::CACHE_TAG . '_' . $block->getIdentifier());
        }

        $this->assertEqualsCanonicalizing(
            $expectedTags,
            $tags
        );
    }

    /**
     * @param array $response
     * @param BlockInterface[] $blocks
     * @return string
     */
    private function getResolverCacheKeyFromResponseAndBlocks(array $response, array $blocks): string
    {
        $cacheIdValue = $response['headers'][CacheIdCalculator::CACHE_ID_HEADER];

        $blockIdentifiers = array_map(function (BlockInterface $block) {
            return $block->getIdentifier();
        }, $blocks);

        print_r($this->getBlockIdentifiersListAsString($blockIdentifiers));

        $cacheIdQueryPayloadMetadata = sprintf('CmsBlocks%s', json_encode([
            'identifiers' => $blockIdentifiers,
        ]));

        echo $cacheIdQueryPayloadMetadata, PHP_EOL;

        $cacheIdParts = [
            GraphQlCache::CACHE_TAG,
            $cacheIdValue,
            sha1($cacheIdQueryPayloadMetadata)
        ];

        // strtoupper is called in \Magento\Framework\Cache\Frontend\Adapter\Zend::_unifyId
        return strtoupper(implode('_', $cacheIdParts));
    }
}
