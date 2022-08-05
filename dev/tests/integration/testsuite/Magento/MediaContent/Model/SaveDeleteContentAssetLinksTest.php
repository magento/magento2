<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
declare(strict_types=1);

namespace Magento\MediaContent\Model;

use Magento\MediaContentApi\Api\SaveContentAssetLinksInterface;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;
use Magento\MediaContentApi\Api\Data\ContentAssetLinkInterface;
use Magento\MediaContentApi\Api\GetAssetIdsByContentIdentityInterface;
use Magento\MediaContentApi\Api\GetContentByAssetIdsInterface;
use Magento\MediaContentApi\Api\DeleteContentAssetLinksInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for AssignAssets service
 */
class SaveDeleteContentAssetLinksTest extends TestCase
{
    /**
     * @var SaveContentAssetLinksInterface
     */
    private $saveContentAssetLinks;

    /**
     * @var GetAssetIdsByContentIdentityInterface
     */
    private $getAssetIdsByContentIdentity;

    /**
     * @var GetContentByAssetIdsInterface
     */
    private $getContentByAssetIds;

    /**
     * @var DeleteContentAssetLinksInterface
     */
    private $deleteContentAssetLinks;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->saveContentAssetLinks = Bootstrap::getObjectManager()->get(SaveContentAssetLinksInterface::class);
        $this->getAssetIdsByContentIdentity = Bootstrap::getObjectManager()
            ->get(GetAssetIdsByContentIdentityInterface::class);
        $this->getContentByAssetIds = Bootstrap::getObjectManager()->get(GetContentByAssetIdsInterface::class);
        $this->deleteContentAssetLinks = Bootstrap::getObjectManager()->get(DeleteContentAssetLinksInterface::class);
    }

    /**
     * Save asset to content links, retrieve the data, delete assets to content links
     */
    public function testAssignRetrieveAndUnassign(): void
    {
        $entityType = 'catalog_product';
        $entityId = 42;
        $field = 'description';
        $assetIds = [56, 78];

        $contentIdentity = Bootstrap::getObjectManager()->create(
            ContentIdentityInterface::class,
            [
                'entityType' => $entityType,
                'entityId' => $entityId,
                'field' => $field
            ]
        );

        $contentAssetLinks = [];

        foreach ($assetIds as $assetId) {
            $contentAssetLinks[] = Bootstrap::getObjectManager()->create(
                ContentAssetLinkInterface::class,
                [
                    'assetId' => $assetId,
                    'contentIdentity' => $contentIdentity
                ]
            );
        }

        $this->saveContentAssetLinks->execute($contentAssetLinks);
        $retrievedAssetIds = $this->getAssetIdsByContentIdentity->execute($contentIdentity);
        $this->assertEquals($assetIds, $retrievedAssetIds);
        $retrievedContentIdentities = $this->getContentByAssetIds->execute($assetIds);
        $this->assertEquals(count($retrievedContentIdentities), 1);

        foreach ($retrievedContentIdentities as $identity) {
            $this->assertEquals($entityType, $identity->getEntityType());
            $this->assertEquals($entityId, $identity->getEntityId());
            $this->assertEquals($field, $identity->getField());
        }

        $this->deleteContentAssetLinks->execute($contentAssetLinks);

        $this->assertEmpty($this->getContentByAssetIds->execute($assetIds));
        $this->assertEmpty($this->getAssetIdsByContentIdentity->execute($contentIdentity));
    }
}
