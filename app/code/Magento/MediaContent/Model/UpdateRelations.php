<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContent\Model;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\IntegrationException;
use Magento\MediaContentApi\Api\AssignAssetsInterface;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;
use Magento\MediaContentApi\Api\ExtractAssetsFromContentInterface;
use Magento\MediaContentApi\Api\GetAssetIdsUsedInContentInterface;
use Magento\MediaContentApi\Api\UnassignAssetsInterface;
use Magento\MediaContentApi\Api\UpdateRelationsInterface;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Psr\Log\LoggerInterface;

/**
 * Process relation managing between media asset and content: assign or unassign relation if exists.
 */
class UpdateRelations implements UpdateRelationsInterface
{
    /**
     * @var ExtractAssetsFromContentInterface
     */
    private $extractAssetFromContent;

    /**
     * @var AssignAssetsInterface
     */
    private $assignAsset;

    /**
     * @var GetAssetIdsUsedInContentInterface
     */
    private $getAssetsUsedInContent;

    /**
     * @var UnassignAssetsInterface
     */
    private $unassignAsset;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ExtractAssetsFromContentInterface $extractAssetFromContent
     * @param AssignAssetsInterface $assignAsset
     * @param GetAssetIdsUsedInContentInterface $getAssetsUsedInContent
     * @param UnassignAssetsInterface $unassignAsset
     * @param LoggerInterface $logger
     */
    public function __construct(
        ExtractAssetsFromContentInterface $extractAssetFromContent,
        AssignAssetsInterface $assignAsset,
        GetAssetIdsUsedInContentInterface $getAssetsUsedInContent,
        UnassignAssetsInterface $unassignAsset,
        LoggerInterface $logger
    ) {
        $this->extractAssetFromContent = $extractAssetFromContent;
        $this->assignAsset = $assignAsset;
        $this->getAssetsUsedInContent = $getAssetsUsedInContent;
        $this->unassignAsset = $unassignAsset;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(ContentIdentityInterface $contentIdentity, string $data): void
    {
        try {
            $this->updateRelation($contentIdentity, $data);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }
    }

    /**
     * Find out which relations are obsolete and which are new and update them
     *
     * @param ContentIdentityInterface $contentIdentity
     * @param string $data
     * @throws CouldNotDeleteException
     * @throws CouldNotSaveException
     * @throws IntegrationException
     */
    private function updateRelation(ContentIdentityInterface $contentIdentity, string $data)
    {
        $existingAssetIds = $this->getAssetsUsedInContent->execute($contentIdentity);
        $currentAssets = $this->extractAssetFromContent->execute($data);
        /** @var AssetInterface $asset */
        foreach ($currentAssets as $asset) {
            if (!in_array($asset->getId(), $existingAssetIds)) {
                $this->assignAsset->execute($contentIdentity, [$asset->getId()]);
            }
        }

        foreach ($existingAssetIds as $assetId) {
            if (!isset($currentAssets[$assetId])) {
                $this->unassignAsset->execute($contentIdentity, [$assetId]);
            }
        }
    }
}
