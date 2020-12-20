<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentSynchronizationCatalog\Test\Integration\Model\Synchronizer;

use Magento\Framework\Exception\IntegrationException;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterfaceFactory;
use Magento\MediaContentApi\Api\GetAssetIdsByContentIdentityInterface;
use Magento\MediaContentApi\Api\GetContentByAssetIdsInterface;
use Magento\MediaContentSynchronizationApi\Api\SynchronizeIdentitiesInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for catalog SynchronizeIdentities.
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
     * @dataProvider filesProvider
     * @magentoDataFixture Magento/MediaContentCatalog/_files/category_with_asset.php
     * @magentoDataFixture Magento/MediaContentCatalog/_files/product_with_asset.php
     * @magentoDataFixture Magento/MediaGallery/_files/media_asset.php
     * @param ContentIdentityInterface[] $mediaContentIdentities
     * @throws IntegrationException
     */
    public function testExecute(array $mediaContentIdentities): void
    {
        $assetId = 2020;

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
     * Data provider
     *
     * @return array
     */
    public function filesProvider(): array
    {
        return [
            [
                [
                    [
                        'entityType' => 'catalog_category',
                        'field' => 'description',
                        'entityId' => 28767
                    ],
                    [
                        'entityType' => 'catalog_product',
                        'field' => 'description',
                        'entityId' => 1567
                    ]
                ]
            ]
        ];
    }
}
