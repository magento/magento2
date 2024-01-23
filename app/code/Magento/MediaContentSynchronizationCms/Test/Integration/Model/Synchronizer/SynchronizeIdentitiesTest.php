<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentSynchronizationCms\Test\Integration\Model\Synchronizer;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterfaceFactory;
use Magento\MediaContentApi\Api\GetAssetIdsByContentIdentityInterface;
use Magento\MediaContentApi\Api\GetContentByAssetIdsInterface;
use Magento\MediaContentSynchronizationApi\Api\SynchronizeIdentitiesInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for CMS SynchronizeIdentities.
 */
class SynchronizeIdentitiesTest extends TestCase
{
    private const ENTITY_TYPE = 'entityType';
    private const ENTITY_ID = 'entityId';
    private const FIELD = 'field';

    /**
     * @var ContentIdentityInterfaceFactory
     */
    private $contentIdentityFactory;

    /**
     * @var GetAssetIdsByContentIdentityInterface
     */
    private $getAssetIds;

    /**
     * @var GetContentByAssetIdsInterface
     */
    private $getContentIdentities;

    /**
     * @var SynchronizeIdentitiesInterface
     */
    private $synchronizeIdentities;

    protected function setUp(): void
    {
        $this->contentIdentityFactory = Bootstrap::getObjectManager()->get(ContentIdentityInterfaceFactory::class);
        $this->getAssetIds = Bootstrap::getObjectManager()->get(GetAssetIdsByContentIdentityInterface::class);
        $this->synchronizeIdentities = Bootstrap::getObjectManager()->get(SynchronizeIdentitiesInterface::class);
        $this->getContentIdentities = Bootstrap::getObjectManager()->get(GetContentByAssetIdsInterface::class);
    }

    /**
     * @magentoDataFixture Magento/MediaContentCms/_files/page_with_asset.php
     * @magentoDataFixture Magento/MediaContentCms/_files/block_with_asset.php
     * @magentoDataFixture Magento/MediaGallery/_files/media_asset.php
     * @throws IntegrationException
     * @throws LocalizedException
     */
    public function testExecute(): void
    {
        $assetId = 2020;
        $pageId = $this->getPage('fixture_page_with_asset')->getId();
        $blockId = $this->getBlock('fixture_block_with_asset')->getId();
        $mediaContentIdentities = [
            [
                'entityType' => 'cms_page',
                'field' => 'content',
                'entityId' => $pageId
            ],
            [
                'entityType' => 'cms_block',
                'field' => 'content',
                'entityId' => $blockId
            ]
        ];

        $contentIdentities = [];
        foreach ($mediaContentIdentities as $mediaContentIdentity) {
            $contentIdentities[] = $this->contentIdentityFactory->create(
                [
                    self::ENTITY_TYPE => $mediaContentIdentity[self::ENTITY_TYPE],
                    self::ENTITY_ID => $mediaContentIdentity[self::ENTITY_ID],
                    self::FIELD => $mediaContentIdentity[self::FIELD]
                ]
            );
        }

        $this->assertNotEmpty($contentIdentities);
        $this->assertEmpty($this->getContentIdentities->execute([$assetId]));
        $this->synchronizeIdentities->execute($contentIdentities);

        $entityIds = [];
        foreach ($contentIdentities as $contentIdentity) {
            $this->assertEquals([$assetId], $this->getAssetIds->execute($contentIdentity));
            $entityIds[] = $contentIdentity->getEntityId();
        }

        $synchronizedContentIdentities = $this->getContentIdentities->execute([$assetId]);
        $this->assertEquals(2, count($synchronizedContentIdentities));

        foreach ($synchronizedContentIdentities as $syncedContentIdentity) {
            $this->assertContains($syncedContentIdentity->getEntityId(), $entityIds);
        }
    }

    /**
     * Get fixture block
     *
     * @param string $identifier
     * @return BlockInterface
     * @throws LocalizedException
     */
    private function getBlock(string $identifier): BlockInterface
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var BlockRepositoryInterface $blockRepository */
        $blockRepository = $objectManager->get(BlockRepositoryInterface::class);

        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter(BlockInterface::IDENTIFIER, $identifier)
            ->create();

        return current($blockRepository->getList($searchCriteria)->getItems());
    }

    /**
     * Get fixture page
     *
     * @param string $identifier
     * @return PageInterface
     * @throws LocalizedException
     */
    private function getPage(string $identifier): PageInterface
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var PageRepositoryInterface $repository */
        $repository = $objectManager->get(PageRepositoryInterface::class);

        /** @var SearchCriteriaBuilder $searchCriteriaBuilder */
        $searchCriteriaBuilder = $objectManager->get(SearchCriteriaBuilder::class);
        $searchCriteria = $searchCriteriaBuilder->addFilter(PageInterface::IDENTIFIER, $identifier)
            ->create();

        return current($repository->getList($searchCriteria)->getItems());
    }
}
