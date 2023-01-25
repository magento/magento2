<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
declare(strict_types=1);

namespace Magento\MediaContentSynchronizationCatalog\Test\Integration\Model\Synchronizer;

use Magento\MediaContentApi\Api\Data\ContentIdentityInterfaceFactory;
use Magento\MediaContentApi\Api\GetAssetIdsByContentIdentityInterface;
use Magento\MediaContentApi\Api\GetContentByAssetIdsInterface;
use Magento\MediaContentSynchronizationCatalog\Model\Synchronizer\Category;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for categories synchronization
 */
class CategoryTest extends TestCase
{
    /**
     * @var Category
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
        $this->synchronizer = Bootstrap::getObjectManager()->get(Category::class);
        $this->getAssetIds = Bootstrap::getObjectManager()->get(GetAssetIdsByContentIdentityInterface::class);
        $this->getContentIdentities = Bootstrap::getObjectManager()->get(GetContentByAssetIdsInterface::class);
        $this->contentIdentityFactory = Bootstrap::getObjectManager()->get(ContentIdentityInterfaceFactory::class);
    }

    /**
     * Test synchronization between category and media assets (fixtures sequence does matter)
     *
     * @magentoDataFixture Magento/MediaContentCatalog/_files/category_with_asset.php
     * @magentoDataFixture Magento/MediaGallery/_files/media_asset.php
     */
    public function testExecute(): void
    {
        $assetId = 2020;
        $categoryId = 28767;
        $contentIdentity = $this->contentIdentityFactory->create(
            [
                'entityType' => 'catalog_category',
                'field' => 'description',
                'entityId' => $categoryId
            ]
        );

        $this->assertEmpty($this->getContentIdentities->execute([$assetId]));
        $this->assertEmpty($this->getAssetIds->execute($contentIdentity));

        $this->synchronizer->execute();

        $this->assertEquals([$assetId], $this->getAssetIds->execute($contentIdentity));

        $synchronizedContentIdentities = $this->getContentIdentities->execute([$assetId]);
        $this->assertEquals(1, count($synchronizedContentIdentities));
        foreach ($synchronizedContentIdentities as $syncedContentIdentity) {
            $this->assertEquals($categoryId, $syncedContentIdentity->getEntityId());
        }
    }
}
