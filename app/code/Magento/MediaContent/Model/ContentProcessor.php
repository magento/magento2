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
use Magento\MediaContentApi\Api\AssignAssetInterface;
use Magento\MediaContentApi\Api\GetAssetsUsedInContentInterface;
use Magento\MediaContentApi\Api\UnassignAssetInterface;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Psr\Log\LoggerInterface;

/**
 * Process relation managing between media asset and content: assign or unassign relation if exists.
 */
class ContentProcessor
{
    /**
     * @var ExtractAssetFromContent
     */
    private $extractAssetFromContent;

    /**
     * @var AssignAssetInterface
     */
    private $assignAsset;

    /**
     * @var GetAssetsUsedInContentInterface
     */
    private $getAssetsUsedInContent;

    /**
     * @var UnassignAssetInterface
     */
    private $unassignAsset;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * ContentProcessor constructor.
     *
     * @param ExtractAssetFromContent $extractAssetFromContent
     * @param AssignAssetInterface $assignAsset
     * @param GetAssetsUsedInContentInterface $getAssetsUsedInContent
     * @param UnassignAssetInterface $unassignAsset
     * @param LoggerInterface $logger
     */
    public function __construct(
        ExtractAssetFromContent $extractAssetFromContent,
        AssignAssetInterface $assignAsset,
        GetAssetsUsedInContentInterface $getAssetsUsedInContent,
        UnassignAssetInterface $unassignAsset,
        LoggerInterface $logger
    ) {
        $this->extractAssetFromContent = $extractAssetFromContent;
        $this->assignAsset = $assignAsset;
        $this->getAssetsUsedInContent = $getAssetsUsedInContent;
        $this->unassignAsset = $unassignAsset;
        $this->logger = $logger;
    }

    /**
     * Create new relation between media asset and content or updated existing
     *
     * @param string $type
     * @param string $field
     * @param string $entityId
     * @param string $data
     */
    public function execute(string $type, string $field, string $entityId, string $data): void
    {
        try {
            $this->updateRelation($type, $field, $entityId, $data);
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
        }
    }

    /**
     * Records a relation for the newly added asset
     *
     * @param string $type
     * @param string $field
     * @param string $entityId
     * @param string $data
     * @throws CouldNotDeleteException
     * @throws CouldNotSaveException
     * @throws IntegrationException
     */
    private function updateRelation(string $type, string $field, string $entityId, string $data)
    {
        $relations = $this->getAssetsUsedInContent->execute($type, $entityId, $field);
        $assetsInContent = $this->extractAssetFromContent->execute($data);
        /** @var AssetInterface $asset */
        foreach ($assetsInContent as $asset) {
            if (!isset($relations[$asset->getId()])) {
                $this->assignAsset->execute($asset->getId(), $type, $entityId, $field);
            }
        }

        foreach (array_keys($relations) as $assetId) {
            if (!isset($assetsInContent[$assetId])) {
                $this->unassignAsset->execute($assetId, $type, $entityId, $field);
            }
        }
    }
}
