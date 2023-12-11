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
use Magento\MediaContentApi\Api\SaveContentAssetLinksInterface;
use Magento\MediaContentApi\Api\DeleteContentAssetLinksInterface;
use Magento\MediaContentApi\Api\Data\ContentAssetLinkInterfaceFactory;
use Magento\MediaContentApi\Api\Data\ContentIdentityInterface;
use Magento\MediaContentApi\Api\ExtractAssetsFromContentInterface;
use Magento\MediaContentApi\Api\GetAssetIdsByContentIdentityInterface;
use Magento\MediaContentApi\Api\UpdateContentAssetLinksInterface;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Psr\Log\LoggerInterface;

/**
 * Process relation managing between media asset and content: create or delete link if exists.
 */
class UpdateContentAssetLinks implements UpdateContentAssetLinksInterface
{
    private const ASSET_ID = 'assetId';
    private const CONTENT_IDENTITY = 'contentIdentity';

    /**
     * @var ExtractAssetsFromContentInterface
     */
    private $extractAssetFromContent;

    /**
     * @var SaveContentAssetLinksInterface
     */
    private $saveContentAssetLinks;

    /**
     * @var DeleteContentAssetLinksInterface
     */
    private $deleteContentAssetLinks;

    /**
     * @var ContentAssetLinkInterfaceFactory
     */
    private $contentAssetLinkFactory;

    /**
     * @var GetAssetIdsByContentIdentityInterface
     */
    private $getAssetIdsByContentIdentity;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ExtractAssetsFromContentInterface $extractAssetFromContent
     * @param SaveContentAssetLinksInterface $saveContentAssetLinks
     * @param DeleteContentAssetLinksInterface $deleteContentAssetLinks
     * @param ContentAssetLinkInterfaceFactory $contentAssetLinkFactory
     * @param GetAssetIdsByContentIdentityInterface $getAssetIdsByContentIdentity
     * @param LoggerInterface $logger
     */
    public function __construct(
        ExtractAssetsFromContentInterface $extractAssetFromContent,
        SaveContentAssetLinksInterface $saveContentAssetLinks,
        DeleteContentAssetLinksInterface $deleteContentAssetLinks,
        ContentAssetLinkInterfaceFactory $contentAssetLinkFactory,
        GetAssetIdsByContentIdentityInterface $getAssetIdsByContentIdentity,
        LoggerInterface $logger
    ) {
        $this->extractAssetFromContent = $extractAssetFromContent;
        $this->saveContentAssetLinks = $saveContentAssetLinks;
        $this->deleteContentAssetLinks = $deleteContentAssetLinks;
        $this->contentAssetLinkFactory = $contentAssetLinkFactory;
        $this->getAssetIdsByContentIdentity = $getAssetIdsByContentIdentity;
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
        $existingAssetIds = $this->getAssetIdsByContentIdentity->execute($contentIdentity);
        $currentAssets = $this->extractAssetFromContent->execute($data);
        /** @var AssetInterface $asset */
        foreach ($currentAssets as $asset) {
            if (!in_array($asset->getId(), $existingAssetIds)) {
                $contentAssetLink = $this->contentAssetLinkFactory->create([
                    self::ASSET_ID => $asset->getId(),
                    self::CONTENT_IDENTITY => $contentIdentity
                ]);
                $this->saveContentAssetLinks->execute([$contentAssetLink]);
            }
        }

        foreach ($existingAssetIds as $assetId) {
            if (!isset($currentAssets[$assetId])) {
                $contentAssetLink = $this->contentAssetLinkFactory->create([
                    self::ASSET_ID => $assetId,
                    self::CONTENT_IDENTITY => $contentIdentity
                ]);
                $this->deleteContentAssetLinks->execute([$contentAssetLink]);
            }
        }
    }
}
