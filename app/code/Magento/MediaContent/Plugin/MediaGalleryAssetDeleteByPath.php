<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContent\Plugin;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\MediaContentApi\Api\DeleteContentAssetLinksByAssetIdsInterface;
use Magento\MediaGalleryApi\Model\Asset\Command\DeleteByPathInterface;
use Magento\MediaGalleryApi\Model\Asset\Command\GetByPathInterface;
use Psr\Log\LoggerInterface;

/**
 * Remove media content record after media gallery asset removal.
 */
class MediaGalleryAssetDeleteByPath
{
    /**
     * @var GetByPathInterface
     */
    private $getByPath;

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
     * @param GetByPathInterface $getByPath
     * @param LoggerInterface $logger
     */
    public function __construct(
        DeleteContentAssetLinksByAssetIdsInterface $deleteContentAssetLinksByAssetIds,
        GetByPathInterface $getByPath,
        LoggerInterface $logger
    ) {
        $this->deleteContentAssetLinksByAssetIds = $deleteContentAssetLinksByAssetIds;
        $this->getByPath = $getByPath;
        $this->logger = $logger;
    }

    /**
     * Around plugin on execute method
     *
     * @param DeleteByPathInterface $subject
     * @param \Closure $proceed
     * @param string $mediaAssetPath
     * @throws CouldNotDeleteException
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        DeleteByPathInterface $subject,
        \Closure $proceed,
        string $mediaAssetPath
    ) : void {
        $asset = $this->getByPath->execute($mediaAssetPath);

        $proceed($mediaAssetPath);

        $this->deleteContentAssetLinksByAssetIds->execute([$asset->getId()]);
    }
}
