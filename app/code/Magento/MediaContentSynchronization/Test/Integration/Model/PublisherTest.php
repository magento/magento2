<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentSynchronization\Test\Integration\Model;

use Magento\Framework\Exception\IntegrationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\MessageQueue\ConsumerInterface;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterfaceFactory;
use Magento\MediaContentApi\Api\GetAssetIdsByContentIdentityInterface;
use Magento\MediaContentApi\Api\GetContentByAssetIdsInterface;
use Magento\MediaContentSynchronization\Model\Publish;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\Framework\MessageQueue\ConsumerFactory;

/**
 * Test for media content Publisher
 */
class PublisherTest extends TestCase
{
    private const TOPIC_MEDIA_CONTENT_SYNCHRONIZATION = 'media.content.synchronization';

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
     * @var Publish
     */
    private $publish;

    /**
     * @var ConsumerInterface
     */
    private $consumer;

    /**
     * @var ConsumerFactory
     */
    private $consumerFactory;

    protected function setUp(): void
    {
        $this->contentIdentityFactory = Bootstrap::getObjectManager()->get(ContentIdentityInterfaceFactory::class);
        $this->publish = Bootstrap::getObjectManager()->get(Publish::class);
        $this->getAssetIds = Bootstrap::getObjectManager()->get(GetAssetIdsByContentIdentityInterface::class);
        $this->getContentIdentities = Bootstrap::getObjectManager()->get(GetContentByAssetIdsInterface::class);
        $this->consumerFactory = Bootstrap::getObjectManager()->get(ConsumerFactory::class);
        $this->consumer = Bootstrap::getObjectManager()->get(ConsumerInterface::class);
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
        $consumerName = self::TOPIC_MEDIA_CONTENT_SYNCHRONIZATION;
        $this->consumerFactory->get(self::TOPIC_MEDIA_CONTENT_SYNCHRONIZATION);
        $this->consumer->process();

        // verify synchronized media content
        foreach ($contentIdentities as $contentIdentity) {
            $assetId = 2020;
            $contentIdentityObject = $this->contentIdentityFactory->create($contentIdentity);
            var_dump($this->getAssetIds->execute($contentIdentityObject));
            $this->assertEquals([$assetId], $this->getAssetIds->execute($contentIdentityObject));
            $synchronizedContentIdentities = $this->getContentIdentities->execute([$assetId]);
            var_dump(count($synchronizedContentIdentities));
            $this->assertEquals(4, count($synchronizedContentIdentities));

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
                        'entityType' => 'catalog_category',
                        'field' => 'description',
                        'entityId' => 28767
                    ],
                    [
                        'entityType' => 'catalog_product',
                        'field' => 'description',
                        'entityId' => 1567
                    ],
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
