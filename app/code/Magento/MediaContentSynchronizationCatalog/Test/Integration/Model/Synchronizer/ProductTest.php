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
use Magento\MediaContentSynchronizationCatalog\Model\Synchronizer\Product;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for products synchronization
 */
class ProductTest extends TestCase
{
    /**
     * @var Product
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
        $this->synchronizer = Bootstrap::getObjectManager()->get(Product::class);
        $this->getAssetIds = Bootstrap::getObjectManager()->get(GetAssetIdsByContentIdentityInterface::class);
        $this->getContentIdentities = Bootstrap::getObjectManager()->get(GetContentByAssetIdsInterface::class);
        $this->contentIdentityFactory = Bootstrap::getObjectManager()->get(ContentIdentityInterfaceFactory::class);
    }

    /**
     * Test synchronization between products and media assets (fixtures sequence does matter)
     *
     * @magentoDataFixture Magento/MediaContentCatalog/_files/product_with_asset.php
     * @magentoDataFixture Magento/MediaGallery/_files/media_asset.php
     */
    public function testExecute(): void
    {
        $assetId = 2020;
        $productId = 1567;
        $contentIdentity = $this->contentIdentityFactory->create(
            [
                'entityType' => 'catalog_product',
                'field' => 'description',
                'entityId' => $productId
            ]
        );

        $this->assertEmpty($this->getContentIdentities->execute([$assetId]));
        $this->assertEmpty($this->getAssetIds->execute($contentIdentity));

        $this->synchronizer->execute();

        $this->assertEquals([$assetId], $this->getAssetIds->execute($contentIdentity));

        $synchronizedContentIdentities = $this->getContentIdentities->execute([$assetId]);
        $this->assertEquals(2, count($synchronizedContentIdentities));
        foreach ($synchronizedContentIdentities as $syncedContentIdentity) {
            $this->assertEquals($productId, $syncedContentIdentity->getEntityId());
        }
    }
}
