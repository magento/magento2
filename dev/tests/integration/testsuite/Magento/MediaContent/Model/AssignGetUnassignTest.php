<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 *
 */
declare(strict_types=1);

namespace Magento\MediaContent\Model;

use Magento\MediaContentApi\Api\AssignAssetsInterface;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;
use Magento\MediaContentApi\Api\GetAssetIdsUsedInContentInterface;
use Magento\MediaContentApi\Api\GetContentWithAssetsInterface;
use Magento\MediaContentApi\Api\UnassignAssetsInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for AssignAssets service
 */
class AssignGetUnassignTest extends TestCase
{
    /**
     * @var AssignAssetsInterface
     */
    private $assign;

    /**
     * @var GetAssetIdsUsedInContentInterface
     */
    private $getAssetIds;

    /**
     * @var GetContentWithAssetsInterface
     */
    private $getContent;

    /**
     * @var UnassignAssetsInterface
     */
    private $unassign;

    /**
     * @inheritdoc
     */
    public function setUp(): void
    {
        $this->assign = Bootstrap::getObjectManager()->create(AssignAssetsInterface::class);
        $this->getAssetIds = Bootstrap::getObjectManager()->create(GetAssetIdsUsedInContentInterface::class);
        $this->getContent = Bootstrap::getObjectManager()->create(GetContentWithAssetsInterface::class);
        $this->unassign = Bootstrap::getObjectManager()->create(UnassignAssetsInterface::class);
    }

    /**
     * Assing assets to content, retrieve the data, then unassign assets from content
     */
    public function testAssignRetrieveAndUnassign(): void
    {
        $entityType = 'catalog_product';
        $entityId = '42';
        $field = 'description';
        $assetIds = [56, 78];

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

        $this->assign->execute($contentIdentity, $assetIds);

        $retrievedAssetIds = $this->getAssetIds->execute($contentIdentity);
        $this->assertEquals($assetIds, $retrievedAssetIds);

        $retrievedContentIdentities = $this->getContent->execute($assetIds);

        $this->assertEquals(count($retrievedContentIdentities), 1);

        foreach ($retrievedContentIdentities as $identity) {
            $this->assertEquals($entityType, $identity->getEntityType());
            $this->assertEquals($entityId, $identity->getEntityId());
            $this->assertEquals($field, $identity->getField());
        }

        $this->unassign->execute($contentIdentity, $assetIds);

        $this->assertEmpty($this->getContent->execute($assetIds));
        $this->assertEmpty($this->getAssetIds->execute($contentIdentity));
    }
}
