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

    protected function setUp(): void
    {
        $objectManager = ObjectManager::getInstance();
        $this->blockRepository = $objectManager->get(BlockRepositoryInterface::class);
        $this->graphqlCache = $objectManager->get(GraphQlCache::class);
        $this->widgetFilter = $objectManager->get(FilterEmulate::class);
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

    private function getQuery(array $identifiers): string
    {
        $identifiersStr = $this->getBlockIdentifiersListAsString($identifiers);

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
    private function getBlockIdentifiersListAsString(array $identifiers): string
    {
        return implode(',', $identifiers);
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
            'identifiers' => [$this->getBlockIdentifiersListAsString($blockIdentifiers)],
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

//    private function getBlockBy(string $title): PageInterface
//    {
//        $searchCriteria = $this->searchCriteriaBuilder
//            ->addFilter('title', $title)
//            ->create();
//
//        $blocks = $this->blockRepository->getList($searchCriteria)->getItems();
//
//        /** @var BlockInterface $blocks */
//        $block = reset($blocks);
//
//        return $block;
//    }
}
