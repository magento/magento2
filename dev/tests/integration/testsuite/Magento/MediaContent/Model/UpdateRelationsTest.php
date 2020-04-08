<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
declare(strict_types=1);

namespace Magento\MediaContent\Model;

use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;
use Magento\MediaContentApi\Api\GetAssetIdsUsedInContentInterface;
use Magento\MediaContentApi\Api\UpdateRelationsInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for ExtractAssetsFromContent
 */
class UpdateRelationsTest extends TestCase
{
    /**
     * @var UpdateRelationsInterface
     */
    private $service;

    /**
     * @var GetAssetIdsUsedInContentInterface
     */
    private $getAssetIds;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->service = Bootstrap::getObjectManager()->create(UpdateRelationsInterface::class);
        $this->getAssetIds = Bootstrap::getObjectManager()->create(GetAssetIdsUsedInContentInterface::class);
    }

    /**
     * Assing assets to content, retrieve the data, then unassign assets from content
     *
     * @magentoDataFixture Magento/MediaGallery/_files/media_asset.php
     */
    public function testExecute(): void
    {
        $entityType = 'catalog_product';
        $entityId = '42';
        $field = 'description';
        $contentWithoutAsset = '';
        $contentWithAsset = 'content {{media url="testDirectory/path.jpg"}} content';

        $contentIdentity = Bootstrap::getObjectManager()->create(
            ContentIdentityInterface::class,
            [
                'data' => [
                    'entity_type' => $entityType,
                    'entity_id' => $entityId,
                    'field' => $field
                ]
            ]
        );

        $this->service->execute($contentIdentity, $contentWithoutAsset);
        $this->assertEmpty($this->getAssetIds->execute($contentIdentity));

        $this->service->execute($contentIdentity, $contentWithAsset);
        $this->assertNotEmpty($this->getAssetIds->execute($contentIdentity));

        $this->service->execute($contentIdentity, $contentWithoutAsset);
        $this->assertEmpty($this->getAssetIds->execute($contentIdentity));
    }
}
