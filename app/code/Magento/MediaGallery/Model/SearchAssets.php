<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\MediaGalleryApi\Api\Data\AssetInterfaceFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\MediaGallery\Model\ResourceModel\GetAssetsBySearchCriteria;
use Magento\MediaGalleryApi\Api\SearchAssetsInterface;

/**
 * Get media assets by searchCriteria
 */
class SearchAssets implements SearchAssetsInterface
{
    /**
     * @var GetAssetsBySearchCriteria
     */
    private $getAssetsBySearchCriteria;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AssetInterfaceFactory
     */
    private $mediaAssetFactory;

    /**
     * @param GetAssetsBySearchCriteria $getAssetsBySearchCriteria
     * @param AssetInterfaceFactory $mediaAssetFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        GetAssetsBySearchCriteria $getAssetsBySearchCriteria,
        AssetInterfaceFactory $mediaAssetFactory,
        LoggerInterface $logger
    ) {
        $this->getAssetsBySearchCriteria = $getAssetsBySearchCriteria;
        $this->mediaAssetFactory = $mediaAssetFactory;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function execute(SearchCriteriaInterface $searchCriteria): array
    {
        $assets = [];
        try {
            foreach ($this->getAssetsBySearchCriteria->execute($searchCriteria)->getItems() as $assetData) {
                $assets[] = $this->mediaAssetFactory->create(
                    [
                        'id' => $assetData['id'],
                        'path' => $assetData['path'],
                        'title' => $assetData['title'],
                        'description' => $assetData['description'],
                        'source' => $assetData['source'],
                        'hash' => $assetData['hash'],
                        'contentType' => $assetData['content_type'],
                        'width' => $assetData['width'],
                        'height' => $assetData['height'],
                        'size' => $assetData['size'],
                        'createdAt' => $assetData['created_at'],
                        'updatedAt' => $assetData['updated_at'],
                    ]
                );
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            throw new LocalizedException(__('Could not retrieve media assets'), $exception->getMessage());
        }
        return $assets;
    }
}
