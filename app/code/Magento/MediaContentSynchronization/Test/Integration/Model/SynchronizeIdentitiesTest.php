<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentSynchronization\Test\Integration\Model;

use Magento\Framework\Exception\IntegrationException;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterfaceFactory;
use Magento\MediaContentApi\Api\GetAssetIdsByContentIdentityInterface;
use Magento\MediaContentApi\Api\GetContentByAssetIdsInterface;
use Magento\MediaContentSynchronizationApi\Api\SynchronizeIdentitiesInterface;
use Magento\MediaContentSynchronizationApi\Api\SynchronizeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for SynchronizeIdentities.
 */
class SynchronizeIdentitiesTest extends TestCase
{
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

    /**
     * @var SynchronizeInterface
     */
    private $synchronize;

    protected function setUp(): void
    {
        $this->contentIdentityFactory = Bootstrap::getObjectManager()->get(ContentIdentityInterfaceFactory::class);
        $this->getAssetIds = Bootstrap::getObjectManager()->get(GetAssetIdsByContentIdentityInterface::class);
        $this->synchronizeIdentities = Bootstrap::getObjectManager()->get(SynchronizeIdentitiesInterface::class);
        $this->synchronize = Bootstrap::getObjectManager()->get(SynchronizeInterface::class);
        $this->getContentIdentities = Bootstrap::getObjectManager()->get(GetContentByAssetIdsInterface::class);
    }

    /**
     * @dataProvider filesProvider
     * @magentoDataFixture Magento/MediaContentCatalog/_files/category_with_asset.php
     * @magentoDataFixture Magento/MediaContentCatalog/_files/product_with_asset.php
     * @magentoDataFixture Magento/MediaContentCms/_files/page_with_asset.php
     * @magentoDataFixture Magento/MediaContentCms/_files/block_with_asset.php
     * @magentoDataFixture Magento/MediaGallery/_files/media_asset.php
     * @param array $mediaContentIdentities
     * @throws IntegrationException
     */
    public function testExecute(array $mediaContentIdentities): void
    {
        $this->assertNotEmpty($mediaContentIdentities);
        $this->synchronizeIdentities->execute($mediaContentIdentities);

        foreach ($mediaContentIdentities as $contentIdentity) {
            $assetId = 2020;
            $identity = $this->contentIdentityFactory->create($contentIdentity);
            $this->assertEquals([$assetId], $this->getAssetIds->execute($identity));

            $synchronizedContentIdentities = $this->getContentIdentities->execute([$assetId]);
            $this->assertEquals(4, count($synchronizedContentIdentities));

            $syncedIds = [];
            foreach ($synchronizedContentIdentities as $syncedContentIdentity) {
                $syncedIds[] = (int)$syncedContentIdentity->getEntityId();
            }
            $this->assertContains($contentIdentity['entityId'], $syncedIds);
        }
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function filesProvider(): array
    {
        return [
            [
                [
                    $this->getCategoryIdentities(),
                    $this->getProductIdentities(),
                    $this->getCmsPageIdentities(),
                    $this->getCmsBlockIdentities()
                ]
            ]
        ];
    }

    /**
     * Format category media content identities
     */
    public function getCategoryIdentities()
    {
        $categoryId = 28767;
        return [
            'entityType' => 'catalog_category',
            'field' => 'description',
            'entityId' => $categoryId
        ];
    }

    /**
     * Format product media content identities
     */
    public function getProductIdentities()
    {
        $productId = 1567;
        return [
            'entityType' => 'catalog_product',
            'field' => 'description',
            'entityId' => $productId
        ];
    }

    /**
     * Format cms page media content identities
     */
    public function getCmsPageIdentities()
    {
        $pageId = 5;
        return [
            'entityType' => 'cms_page',
            'field' => 'content',
            'entityId' => $pageId
        ];
    }

    /**
     * Format cms block media content identities
     */
    public function getCmsBlockIdentities()
    {
        $blockId = 1;
        return [
            'entityType' => 'cms_block',
            'field' => 'content',
            'entityId' => $blockId
        ];
    }
}
