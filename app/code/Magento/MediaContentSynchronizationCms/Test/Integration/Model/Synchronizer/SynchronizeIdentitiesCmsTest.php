<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentSynchronizationCms\Test\Integration\Model\Synchronizer;

use Magento\Framework\Exception\IntegrationException;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterfaceFactory;
use Magento\MediaContentApi\Api\GetAssetIdsByContentIdentityInterface;
use Magento\MediaContentApi\Api\GetContentByAssetIdsInterface;
use Magento\MediaContentSynchronizationApi\Api\SynchronizeIdentitiesInterface;
use Magento\MediaContentSynchronizationApi\Api\SynchronizeInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for CMS SynchronizeIdentities.
 */
class SynchronizeIdentitiesCmsTest extends TestCase
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
     * @magentoDataFixture Magento/MediaContentCms/_files/page_with_asset.php
     * @magentoDataFixture Magento/MediaContentCms/_files/block_with_asset.php
     * @magentoDataFixture Magento/MediaGallery/_files/media_asset.php
     * @param ContentIdentityInterface[] $mediaContentIdentities
     * @throws IntegrationException
     */
    public function testExecute(array $mediaContentIdentities): void
    {
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
        $this->synchronizeIdentities->execute($contentIdentities);

        foreach ($contentIdentities as $contentIdentity) {
            $assetId = 2020;
            $this->assertEquals([$assetId], $this->getAssetIds->execute($contentIdentity));
            $synchronizedContentIdentities = $this->getContentIdentities->execute([$assetId]);
            $this->assertEquals(2, count($synchronizedContentIdentities));

            $syncedIds = [];
            foreach ($synchronizedContentIdentities as $syncedContentIdentity) {
                $syncedIds[] = $syncedContentIdentity->getEntityId();
            }
            $this->assertContains($contentIdentity->getEntityId(), $syncedIds);
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
                        'entityType' => 'cms_page',
                        'field' => 'content',
                        'entityId' => 5
                    ],
                    [
                        'entityType' => 'cms_block',
                        'field' => 'content',
                        'entityId' => 1
                    ]
                ]
            ]
        ];
    }
}
