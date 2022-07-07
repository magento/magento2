<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
declare(strict_types=1);

namespace Magento\MediaContentSynchronizationCms\Test\Integration\Model\Synchronizer;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterfaceFactory;
use Magento\MediaContentApi\Api\GetAssetIdsByContentIdentityInterface;
use Magento\MediaContentApi\Api\GetContentByAssetIdsInterface;
use Magento\MediaContentSynchronizationCms\Model\Synchronizer\Page;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for pages synchronization
 */
class PageTest extends TestCase
{
    /**
     * @var Page
     */
    private $synchronizer;

    /**
     * @var GetAssetIdsByContentIdentityInterface
     */
    private $getAssetIds;

    /**
     * @var GetContentByAssetIdsInterface
     */
    private $getContentIdentities;

    /**
     * @var ContentIdentityInterfaceFactory
     */
    private $contentIdentityFactory;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->synchronizer = Bootstrap::getObjectManager()->get(Page::class);
        $this->getAssetIds = Bootstrap::getObjectManager()->get(GetAssetIdsByContentIdentityInterface::class);
        $this->getContentIdentities = Bootstrap::getObjectManager()->get(GetContentByAssetIdsInterface::class);
        $this->contentIdentityFactory = Bootstrap::getObjectManager()->get(ContentIdentityInterfaceFactory::class);
    }

    /**
     * Test synchronization between pages and media assets (fixtures sequence does matter)
     *
     * @magentoDataFixture Magento/MediaContentCms/_files/page_with_asset.php
     * @magentoDataFixture Magento/MediaGallery/_files/media_asset.php
     */
    public function testExecute(): void
    {
        $assetId = 2020;
        $pageId = $this->getPage('fixture_page_with_asset')->getId();
        $contentIdentity = $this->contentIdentityFactory->create(
            [
                'entityType' => 'cms_page',
                'field' => 'content',
                'entityId' => $pageId
            ]
        );

        $this->assertEmpty($this->getContentIdentities->execute([$assetId]));
        $this->assertEmpty($this->getAssetIds->execute($contentIdentity));

        $this->synchronizer->execute();

        $this->assertEquals([$assetId], $this->getAssetIds->execute($contentIdentity));

        $synchronizedContentIdentities = $this->getContentIdentities->execute([$assetId]);
        $this->assertEquals(1, count($synchronizedContentIdentities));
        $this->assertEquals($pageId, $synchronizedContentIdentities[0]->getEntityId());
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
