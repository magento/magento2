<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CmsGraphQl\Model\Resolver;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Model\Block;
use Magento\CmsGraphQl\Model\Resolver\Blocks;
use Magento\Framework\Exception\LocalizedException;
use Magento\GraphQlResolverCache\Model\Resolver\Result\CacheKey\Calculator\ProviderInterface;
use Magento\GraphQlResolverCache\Model\Resolver\Result\Type as GraphQlResolverCache;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Test\Fixture\Store;
use Magento\Cms\Test\Fixture\Block as BlockFixture;
use Magento\TestFramework\Fixture\Config;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQl\ResolverCacheAbstract;
use Magento\TestFramework\TestCase\GraphQl\ResponseContainsErrorsException;
use Magento\Widget\Model\Template\FilterEmulate;

/**
 * Test for cms block resolver cache
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BlockTest extends ResolverCacheAbstract
{
    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * @var GraphQlResolverCache
     */
    private $graphQlResolverCache;

    /**
     * @var FilterEmulate
     */
    private $widgetFilter;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    protected function setUp(): void
    {
        $objectManager = ObjectManager::getInstance();
        $this->blockRepository = $objectManager->get(BlockRepositoryInterface::class);
        $this->graphQlResolverCache = $objectManager->get(GraphQlResolverCache::class);
        $this->widgetFilter = $objectManager->get(FilterEmulate::class);
        $this->storeManager = $objectManager->get(StoreManagerInterface::class);
        $this->fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();

        parent::setUp();
    }

    #[
        DataFixture(BlockFixture::class, as: 'guest_block')
    ]
    public function testCmsSingleBlockResolverCacheAndInvalidationAsGuest()
    {
        $block = $this->fixtures->get('guest_block');

        $query = $this->getQuery([
            $block->getIdentifier(),
        ]);

        $this->graphQlQueryWithResponseHeaders($query);

        $cacheIdentityString = $this->getResolverCacheKeyFromBlocks([$block]);

        $cacheEntry = $this->graphQlResolverCache->load($cacheIdentityString);
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
            $this->graphQlResolverCache->test($cacheIdentityString),
            'Cache entry should be invalidated after block content change'
        );
    }

    #[
        DataFixture(BlockFixture::class, as: 'block', count: 2)
    ]
    public function testCmsMultipleBlockResolverCacheAndInvalidationAsGuest()
    {
        $block1 = $this->fixtures->get('block1');
        $block2 = $this->fixtures->get('block2');

        $query = $this->getQuery([
            $block1->getIdentifier(),
            $block2->getIdentifier(),
        ]);

        $this->graphQlQueryWithResponseHeaders($query);

        $cacheKey = $this->getResolverCacheKeyFromBlocks([
            $block1,
            $block2,
        ]);

        $cacheEntry = $this->graphQlResolverCache->load($cacheKey);
        $cacheEntryDecoded = json_decode($cacheEntry, true);

        $this->assertEqualsCanonicalizing(
            $this->generateExpectedDataFromBlocks([$block1, $block2]),
            $cacheEntryDecoded
        );

        $this->assertTagsByCacheIdentityAndBlocks(
            $cacheKey,
            [$block1, $block2]
        );

        // assert that cache is invalidated after a single block content change
        $block2->setContent('New Content');
        $this->blockRepository->save($block2);

        $this->assertFalse(
            $this->graphQlResolverCache->test($cacheKey),
            'Cache entry should be invalidated after block content change'
        );
    }

    #[
        DataFixture(BlockFixture::class, as: 'deleted_block')
    ]
    public function testCmsBlockResolverCacheInvalidatesWhenBlockGetsDeleted()
    {
        $block = $this->fixtures->get('deleted_block');

        $query = $this->getQuery([
            $block->getIdentifier(),
        ]);

        $this->graphQlQueryWithResponseHeaders($query);

        $cacheKey = $this->getResolverCacheKeyFromBlocks([$block]);

        $cacheEntry = $this->graphQlResolverCache->load($cacheKey);
        $cacheEntryDecoded = json_decode($cacheEntry, true);

        $this->assertEqualsCanonicalizing(
            $this->generateExpectedDataFromBlocks([$block]),
            $cacheEntryDecoded
        );

        $this->assertTagsByCacheIdentityAndBlocks(
            $cacheKey,
            [$block]
        );

        // assert that cache is invalidated after block deletion
        $this->blockRepository->delete($block);

        $this->assertFalse(
            $this->graphQlResolverCache->test($cacheKey),
            'Cache entry should be invalidated after block deletion'
        );
    }

    #[
        DataFixture(BlockFixture::class, as: 'enabled_block')
    ]
    public function testCmsBlockResolverCacheInvalidatesWhenBlockGetsDisabled()
    {
        $block = $this->fixtures->get('enabled_block');

        $query = $this->getQuery([
            $block->getIdentifier(),
        ]);

        $this->graphQlQueryWithResponseHeaders($query);

        $cacheKey = $this->getResolverCacheKeyFromBlocks([$block]);

        $cacheEntry = $this->graphQlResolverCache->load($cacheKey);
        $cacheEntryDecoded = json_decode($cacheEntry, true);

        $this->assertEqualsCanonicalizing(
            $this->generateExpectedDataFromBlocks([$block]),
            $cacheEntryDecoded
        );

        $this->assertTagsByCacheIdentityAndBlocks(
            $cacheKey,
            [$block]
        );

        // assert that cache is invalidated after block disablement
        $block->setIsActive(false);
        $this->blockRepository->save($block);

        $this->assertFalse(
            $this->graphQlResolverCache->test($cacheKey),
            'Cache entry should be invalidated after block disablement'
        );
    }

    /**
     * @throws LocalizedException
     * @throws \Zend_Cache_Exception
     */
    #[
        DataFixture(BlockFixture::class, as: 'block'),
        DataFixture(Store::class, as: 'second_store'),
    ]
    public function testCmsBlockResolverCacheIsInvalidatedAfterChangingItsStoreView()
    {
        /** @var Block $block */
        $block = $this->fixtures->get('block');

        /** @var \Magento\Store\Model\Store $store */
        $store = $this->fixtures->get('second_store');

        $query = $this->getQuery([
            $block->getIdentifier(),
        ]);

        $this->graphQlQueryWithResponseHeaders($query);

        $cacheKey = $this->getResolverCacheKeyFromBlocks([$block]);

        $cacheEntry = $this->graphQlResolverCache->load($cacheKey);
        $cacheEntryDecoded = json_decode($cacheEntry, true);

        $this->assertEqualsCanonicalizing(
            $cacheEntryDecoded,
            $this->generateExpectedDataFromBlocks([$block])
        );

        $this->assertTagsByCacheIdentityAndBlocks(
            (string)$cacheKey,
            [$block]
        );

        // assert that cache is invalidated after changing block's store view
        $secondStoreViewId = $store->getId();
        $block->setStoreId($secondStoreViewId);
        $this->blockRepository->save($block);

        $this->assertFalse(
            $this->graphQlResolverCache->test($cacheKey),
            'Cache entry should be invalidated after changing block\'s store view'
        );
    }

    /**
     * @magentoConfigFixture default/system/full_page_cache/caching_application 2
     * @return void
     */
    public function testCmsBlockResolverCacheDoesNotSaveNonExistentCmsBlock()
    {
        $nonExistentBlock = ObjectManager::getInstance()->create(BlockInterface::class);
        $nonExistentBlock->setIdentifier('non-existent-block');

        $query = $this->getQuery([$nonExistentBlock->getIdentifier()]);

        try {
            $this->graphQlQueryWithResponseHeaders($query);
            $this->fail('Expected exception was not thrown');
        } catch (ResponseContainsErrorsException $e) {
            // expected exception
        }

        $cacheIdentityString = $this->getResolverCacheKeyFromBlocks([$nonExistentBlock]);

        $this->assertFalse(
            $this->graphQlResolverCache->load($cacheIdentityString)
        );
    }

    #[
        Config('system/full_page_cache/caching_application', '2'),
        DataFixture(BlockFixture::class, as: 'block', count: 2)
    ]
    public function testCmsBlockResolverCacheRetainsEntriesThatHaveNotBeenUpdated()
    {
        // query block1
        $block1 = $this->fixtures->get('block1');

        $queryBlock1 = $this->getQuery([
            $block1->getIdentifier(),
        ]);

        $this->graphQlQueryWithResponseHeaders($queryBlock1);

        $cacheKeyBlock1 = $this->getResolverCacheKeyFromBlocks([$block1]);

        // query block2
        $block2 = $this->fixtures->get('block2');

        $queryBlock2 = $this->getQuery([
            $block2->getIdentifier(),
        ]);

        $this->graphQlQueryWithResponseHeaders($queryBlock2);

        $cacheKeyBlock2 = $this->getResolverCacheKeyFromBlocks([$block2]);

        // assert both cache entries are present
        $this->assertIsNumeric(
            $this->graphQlResolverCache->test($cacheKeyBlock1),
            'Cache entry for block1 should be present'
        );

        $this->assertIsNumeric(
            $this->graphQlResolverCache->test($cacheKeyBlock2),
            'Cache entry for block2 should be present'
        );

        // assert that cache is invalidated after block1 update
        $block1->setContent('Updated content');
        $this->blockRepository->save($block1);

        $this->assertFalse(
            $this->graphQlResolverCache->test($cacheKeyBlock1),
            'Cache entry for block1 should be invalidated after block1 update'
        );

        // assert that cache is not invalidated after block1 update
        $this->assertIsNumeric(
            $this->graphQlResolverCache->test($cacheKeyBlock2),
            'Cache entry for block2 should be present after block1 update'
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
        $lowLevelFrontendCache = $this->graphQlResolverCache->getLowLevelFrontend();
        $cacheIdPrefix = $lowLevelFrontendCache->getOption('cache_id_prefix');
        $metadatas = $lowLevelFrontendCache->getMetadatas($cacheIdentityString);
        $tags = $metadatas['tags'];

        $expectedTags = [
            $cacheIdPrefix . strtoupper(GraphQlResolverCache::CACHE_TAG),
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
    private function getResolverCacheKeyFromBlocks(array $blocks): string
    {
        $resolverMock = $this->getMockBuilder(Blocks::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var ProviderInterface $cacheKeyCalculatorProvider */
        $cacheKeyCalculatorProvider = ObjectManager::getInstance()->get(ProviderInterface::class);
        $cacheKeyFactor = $cacheKeyCalculatorProvider
            ->getKeyCalculatorForResolver($resolverMock)
            ->calculateCacheKey();

        $blockIdentifiers = array_map(function (BlockInterface $block) {
            return $block->getIdentifier();
        }, $blocks);

        $cacheKeyQueryPayloadMetadata = sprintf(Blocks::class . '\Interceptor%s', json_encode([
            'identifiers' => $blockIdentifiers,
        ]));

        $cacheKeyParts = [
            GraphQlResolverCache::CACHE_TAG,
            $cacheKeyFactor,
            sha1($cacheKeyQueryPayloadMetadata)
        ];

        // strtoupper is called in \Magento\Framework\Cache\Frontend\Adapter\Zend::_unifyId
        return strtoupper(implode('_', $cacheKeyParts));
    }
}
