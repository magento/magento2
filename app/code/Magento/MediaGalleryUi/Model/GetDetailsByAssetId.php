<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Model;

use Exception;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaGalleryApi\Api\Data\AssetInterface;
use Magento\MediaGalleryApi\Api\Data\KeywordInterface;
use Magento\MediaGalleryApi\Api\GetAssetsByIdsInterface;
use Magento\MediaGalleryApi\Api\GetAssetsKeywordsInterface;
use Magento\MediaGalleryUi\Ui\Component\Listing\Columns\SourceIconProvider;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Load Media Asset from database by id add all related data to it
 */
class GetDetailsByAssetId
{
    /**
     * @var GetAssetsByIdsInterface
     */
    private $getAssetsById;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SourceIconProvider
     */
    private $sourceIconProvider;

    /**
     * @var GetAssetsKeywordsInterface
     */
    private $getAssetKeywords;

    /**
     * @var GetAssetDetails
     */
    private $getAssetDetails;

    /**
     * @param GetAssetDetails $getAssetDetails
     * @param GetAssetsByIdsInterface $getAssetById
     * @param StoreManagerInterface $storeManager
     * @param SourceIconProvider $sourceIconProvider
     * @param GetAssetsKeywordsInterface $getAssetKeywords
     */
    public function __construct(
        GetAssetDetails $getAssetDetails,
        GetAssetsByIdsInterface $getAssetById,
        StoreManagerInterface $storeManager,
        SourceIconProvider $sourceIconProvider,
        GetAssetsKeywordsInterface $getAssetKeywords
    ) {
        $this->getAssetDetails = $getAssetDetails;
        $this->getAssetsById = $getAssetById;
        $this->storeManager = $storeManager;
        $this->sourceIconProvider = $sourceIconProvider;
        $this->getAssetKeywords = $getAssetKeywords;
    }

    /**
     * Get image details by assets Ids
     *
     * @param array $assetIds
     * @throws LocalizedException
     * @throws Exception
     * @return array
     */
    public function execute(array $assetIds): array
    {
        $assets = $this->getAssetsById->execute($assetIds);

        $details = [];
        foreach ($assets as $asset) {
            $details[$asset->getId()] = [
                'image_url' => $this->getUrl($asset->getPath()),
                'title' => $asset->getTitle(),
                'path' => $asset->getPath(),
                'description' => $asset->getDescription(),
                'id' => $asset->getId(),
                'details' => $this->getAssetDetails->execute($asset),
                'size' => $asset->getSize(),
                'tags' => $this->getKeywords($asset),
                'source' => $asset->getSource() ?
                $this->sourceIconProvider->getSourceIconUrl($asset->getSource()) :
                null,
                'content_type' => strtoupper(str_replace('image/', '', $asset->getContentType())),
            ];
        }
        return $details;
    }

    /**
     * Key asset keywords
     *
     * @param AssetInterface $asset
     * @return string[]
     */
    private function getKeywords(AssetInterface $asset): array
    {
        $assetKeywords = $this->getAssetKeywords->execute([$asset->getId()]);

        if (empty($assetKeywords)) {
            return [];
        }

        $keywords = current($assetKeywords)->getKeywords();

        return array_map(
            function (KeywordInterface $keyword) {
                return $keyword->getKeyword();
            },
            $keywords
        );
    }

    /**
     * Get URL for the provided media asset path
     *
     * @param string $path
     *
     * @return string
     *
     * @throws LocalizedException
     */
    private function getUrl(string $path): string
    {
        /** @var Store $store */
        $store = $this->storeManager->getStore();

        return $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $path;
    }
}
