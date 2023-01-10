<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentSynchronization\Test\Integration\Model;

use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\ConsumerFactory;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterfaceFactory;
use Magento\MediaContentApi\Api\GetAssetIdsByContentIdentityInterface;
use Magento\MediaContentApi\Api\GetContentByAssetIdsInterface;
use Magento\MediaContentSynchronization\Model\Publish;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for media content Publisher
 */
class PublisherTest extends TestCase
{
    private const TOPIC_MEDIA_CONTENT_SYNCHRONIZATION = 'media.content.synchronization';

    /**
     * @var ConsumerFactory
     */
    private $consumerFactory;

    /**
     * @var ContentIdentityInterfaceFactory
     */
    private $contentIdentityFactory;

    /**
     * @var GetContentByAssetIdsInterface
     */
    private $getContentIdentities;

    /**
     * @var GetAssetIdsByContentIdentityInterface
     */
    private $getAssetIds;

    /**
     * @var Publish
     */
    private $publish;

    protected function setUp(): void
    {
        $this->consumerFactory = Bootstrap::getObjectManager()->get(ConsumerFactory::class);
        $this->contentIdentityFactory = Bootstrap::getObjectManager()->get(ContentIdentityInterfaceFactory::class);
        $this->getContentIdentities = Bootstrap::getObjectManager()->get(GetContentByAssetIdsInterface::class);
        $this->getAssetIds = Bootstrap::getObjectManager()->get(GetAssetIdsByContentIdentityInterface::class);
        $this->publish = Bootstrap::getObjectManager()->get(Publish::class);
    }

    /**
     * @dataProvider filesProvider
     * @magentoDataFixture Magento/MediaContentCatalog/_files/category_with_asset.php
     * @magentoDataFixture Magento/MediaContentCatalog/_files/product_with_asset.php
     * @magentoDataFixture Magento/MediaContentCms/_files/page_with_asset.php
     * @magentoDataFixture Magento/MediaContentCms/_files/block_with_asset.php
     * @magentoDataFixture Magento/MediaGallery/_files/media_asset.php
     * @param array $contentIdentities
     * @throws IntegrationException
     * @throws LocalizedException
     */
    public function testExecute(array $contentIdentities): void
    {
        // publish message to the queue
        $this->publish->execute($contentIdentities);

        // run and process message
        $batchSize = 1;
        $maxNumberOfMessages = 1;
        $consumer = $this->consumerFactory->get(self::TOPIC_MEDIA_CONTENT_SYNCHRONIZATION, $batchSize);
        $consumer->process($maxNumberOfMessages);

        // verify synchronized media content
        $assetId = 2020;
        $entityIds = [];
        foreach ($contentIdentities as $contentIdentity) {
            $contentIdentityObject = $this->contentIdentityFactory->create($contentIdentity);
            $this->assertEquals([$assetId], $this->getAssetIds->execute($contentIdentityObject));
            $entityIds[] = $contentIdentityObject->getEntityId();
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
