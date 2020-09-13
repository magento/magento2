<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
declare(strict_types=1);

namespace Magento\MediaContent\Model;

use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;
use Magento\MediaContentApi\Api\GetAssetIdsByContentIdentityInterface;
use Magento\MediaContentApi\Api\UpdateContentAssetLinksInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for UpdateContentAssetLinks
 */
class UpdateContentAssetLinksTest extends TestCase
{
    /**
     * @var UpdateContentAssetLinksInterface
     */
    private $updateContentAssetLinks;

    /**
     * @var GetAssetIdsByContentIdentityInterface
     */
    private $getAssetIdsByContentIdentity;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->updateContentAssetLinks = Bootstrap::getObjectManager()->get(UpdateContentAssetLinksInterface::class);
        $this->getAssetIdsByContentIdentity = Bootstrap::getObjectManager()
            ->get(GetAssetIdsByContentIdentityInterface::class);
    }

    /**
     * Assing assets to content, retrieve the data, then unassign assets from content
     *
     * @magentoDataFixture Magento/MediaGallery/_files/media_asset.php
     */
    public function testExecute(): void
    {
        $entityType = 'catalog_product';
        $entityId = 2020;
        $field = 'description';
        $contentWithoutAsset = '';
        $contentWithAsset = 'content {{media url="testDirectory/path.jpg"}} content';

        $contentIdentity = Bootstrap::getObjectManager()->create(
            ContentIdentityInterface::class,
            [
                'entityType' => $entityType,
                'entityId' => $entityId,
                'field' => $field
            ]
        );

        $this->updateContentAssetLinks->execute($contentIdentity, $contentWithoutAsset);
        $this->assertEmpty($this->getAssetIdsByContentIdentity->execute($contentIdentity));

        $this->updateContentAssetLinks->execute($contentIdentity, $contentWithAsset);
        $this->assertNotEmpty($this->getAssetIdsByContentIdentity->execute($contentIdentity));

        $this->updateContentAssetLinks->execute($contentIdentity, $contentWithoutAsset);
        $this->assertEmpty($this->getAssetIdsByContentIdentity->execute($contentIdentity));
    }
}
