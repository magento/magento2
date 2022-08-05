<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
declare(strict_types=1);

namespace Magento\MediaContentSynchronizationCms\Test\Integration\Model\Synchronizer;

use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Cms\Api\Data\BlockInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterfaceFactory;
use Magento\MediaContentApi\Api\GetAssetIdsByContentIdentityInterface;
use Magento\MediaContentApi\Api\GetContentByAssetIdsInterface;
use Magento\MediaContentSynchronizationCms\Model\Synchronizer\Block;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for blocks synchronization
 */
class BlockTest extends TestCase
{
    /**
     * @var Block
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
        $this->synchronizer = Bootstrap::getObjectManager()->get(Block::class);
        $this->getAssetIds = Bootstrap::getObjectManager()->get(GetAssetIdsByContentIdentityInterface::class);
        $this->getContentIdentities = Bootstrap::getObjectManager()->get(GetContentByAssetIdsInterface::class);
        $this->contentIdentityFactory = Bootstrap::getObjectManager()->get(ContentIdentityInterfaceFactory::class);
    }

    /**
     * Test synchronization between blocks and media assets (fixtures sequence does matter)
     *
     * @magentoDataFixture Magento/MediaContentCms/_files/block_with_asset.php
     * @magentoDataFixture Magento/MediaGallery/_files/media_asset.php
     */
    public function testExecute(): void
    {
        $assetId = 2020;
        $blockId = $this->getBlock('fixture_block_with_asset')->getId();
        $contentIdentity = $this->contentIdentityFactory->create(
            [
                'entityType' => 'cms_block',
                'field' => 'content',
                'entityId' => $blockId
            ]
        );

        $this->assertEmpty($this->getContentIdentities->execute([$assetId]));
        $this->assertEmpty($this->getAssetIds->execute($contentIdentity));

        $this->synchronizer->execute();

        $this->assertEquals([$assetId], $this->getAssetIds->execute($contentIdentity));

        $synchronizedContentIdentities = $this->getContentIdentities->execute([$assetId]);
        $this->assertEquals(1, count($synchronizedContentIdentities));
        $this->assertEquals($blockId, $synchronizedContentIdentities[0]->getEntityId());
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
}
