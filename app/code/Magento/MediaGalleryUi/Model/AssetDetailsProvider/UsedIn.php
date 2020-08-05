<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Model\AssetDetailsProvider;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Exception\IntegrationException;
use Magento\MediaContentApi\Api\GetContentByAssetIdsInterface;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGalleryUi\Model\AssetDetailsProviderInterface;

/**
 * Provide information on which content asset is used in
 */
class UsedIn implements AssetDetailsProviderInterface
{
    /**
     * @var GetContentByAssetIdsInterface
     */
    private $getContent;

    /**
     * @var array
     */
    private $contentTypes;

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @param GetContentByAssetIdsInterface $getContent
     * @param UrlInterface $url
     * @param array $contentTypes
     */
    public function __construct(
        GetContentByAssetIdsInterface $getContent,
        UrlInterface $url,
        array $contentTypes = []
    ) {
        $this->getContent = $getContent;
        $this->url = $url;
        $this->contentTypes = $contentTypes;
    }

    /**
     * Provide information on which content asset is used in
     *
     * @param AssetInterface $asset
     * @return array
     * @throws IntegrationException
     */
    public function execute(AssetInterface $asset): array
    {
        return [
            'title' => __('Used In'),
            'value' => $this->getUsedIn($asset->getId())
        ];
    }

    /**
     * Retrieve assets used in the Content
     *
     * @param int $assetId
     * @return array
     * @throws IntegrationException
     */
    private function getUsedIn(int $assetId): array
    {
        $details = [];

        foreach ($this->getUsedInCounts($assetId) as $type => $number) {
            $details[$type] = $this->contentTypes[$type] ?? ['name' => $type, 'link' => null];
            $details[$type]['number'] = $number;
            $details[$type]['link'] = $details[$type]['link'] ? $this->url->getUrl($details[$type]['link']) : null;
        }

        return array_values($details);
    }

    /**
     * Get used in counts per type
     *
     * @param int $assetId
     * @return int[]
     * @throws IntegrationException
     */
    private function getUsedInCounts(int $assetId): array
    {
        $usedIn = [];
        $entityIds = [];

        $contentIdentities = $this->getContent->execute([$assetId]);

        foreach ($contentIdentities as $contentIdentity) {
            $entityId = $contentIdentity->getEntityId();
            $type = $contentIdentity->getEntityType();

            if (!isset($entityIds[$type])) {
                $usedIn[$type] = 1;
            } elseif ($entityIds[$type]['entity_id'] !== $entityId) {
                ++$usedIn[$type];
            }
            $entityIds[$type]['entity_id'] = $entityId;
        }
        return $usedIn;
    }
}
