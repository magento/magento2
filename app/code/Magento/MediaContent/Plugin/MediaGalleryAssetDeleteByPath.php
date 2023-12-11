<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContent\Plugin;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaContentApi\Api\DeleteContentAssetLinksByAssetIdsInterface;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGalleryApi\Api\DeleteAssetsByPathsInterface;
use Magento\MediaGalleryApi\Api\GetAssetsByPathsInterface;
use Psr\Log\LoggerInterface;

/**
 * Remove media content record after media gallery asset removal.
 */
class MediaGalleryAssetDeleteByPath
{
    /**
     * @var GetAssetsByPathsInterface
     */
    private $getByPaths;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DeleteContentAssetLinksByAssetIdsInterface
     */
    private $deleteContentAssetLinksByAssetIds;

    /**
     * @param DeleteContentAssetLinksByAssetIdsInterface $deleteContentAssetLinksByAssetIds
     * @param GetAssetsByPathsInterface $getByPath
     * @param LoggerInterface $logger
     */
    public function __construct(
        DeleteContentAssetLinksByAssetIdsInterface $deleteContentAssetLinksByAssetIds,
        GetAssetsByPathsInterface $getByPath,
        LoggerInterface $logger
    ) {
        $this->deleteContentAssetLinksByAssetIds = $deleteContentAssetLinksByAssetIds;
        $this->getByPaths = $getByPath;
        $this->logger = $logger;
    }

    /**
     * Around plugin on execute method
     *
     * @param DeleteAssetsByPathsInterface $subject
     * @param \Closure $proceed
     * @param array $paths
     * @throws CouldNotDeleteException
     * @throws LocalizedException
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        DeleteAssetsByPathsInterface $subject,
        \Closure $proceed,
        array $paths
    ) : void {
        $assets = $this->getByPaths->execute($paths);

        $proceed($paths);

        $assetIds = array_map(
            function (AssetInterface $asset) {
                return $asset->getId();
            },
            $assets
        );

        $this->deleteContentAssetLinksByAssetIds->execute($assetIds);
    }
}
