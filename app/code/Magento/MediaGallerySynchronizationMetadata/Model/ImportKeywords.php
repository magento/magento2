<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallerySynchronizationMetadata\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\MediaGalleryApi\Api\Data\AssetKeywordsInterfaceFactory;
use Magento\MediaGalleryApi\Api\Data\KeywordInterface;
use Magento\MediaGalleryApi\Api\Data\KeywordInterfaceFactory;
use Magento\MediaGalleryApi\Api\GetAssetsByPathsInterface;
use Magento\MediaGalleryApi\Api\SaveAssetsKeywordsInterface;
use Magento\MediaGalleryMetadataApi\Api\ExtractMetadataInterface;
use Magento\MediaGallerySynchronizationApi\Model\ImportFilesInterface;

/**
 * import image keywords from file metadata
 */
class ImportKeywords implements ImportFilesInterface
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var KeywordInterfaceFactory
     */
    private $keywordFactory;

    /**
     * @var AssetKeywordsInterfaceFactory
     */
    private $assetKeywordsFactory;

    /**
     * @var ExtractMetadataInterface
     */
    private $extractMetadata;

    /**
     * @var SaveAssetsKeywordsInterface
     */
    private $saveAssetKeywords;

    /**
     * @var GetAssetsByPathsInterface
     */
    private $getAssetsByPaths;

    /**
     * @param Filesystem $filesystem
     * @param KeywordInterfaceFactory $keywordFactory
     * @param ExtractMetadataInterface $extractMetadata
     * @param SaveAssetsKeywordsInterface $saveAssetKeywords
     * @param AssetKeywordsInterfaceFactory $assetKeywordsFactory
     * @param GetAssetsByPathsInterface $getAssetsByPaths
     */
    public function __construct(
        Filesystem $filesystem,
        KeywordInterfaceFactory $keywordFactory,
        ExtractMetadataInterface $extractMetadata,
        SaveAssetsKeywordsInterface $saveAssetKeywords,
        AssetKeywordsInterfaceFactory $assetKeywordsFactory,
        GetAssetsByPathsInterface $getAssetsByPaths
    ) {
        $this->filesystem = $filesystem;
        $this->keywordFactory = $keywordFactory;
        $this->extractMetadata = $extractMetadata;
        $this->saveAssetKeywords = $saveAssetKeywords;
        $this->assetKeywordsFactory = $assetKeywordsFactory;
        $this->getAssetsByPaths = $getAssetsByPaths;
    }
    /**
     * @inheritdoc
     */
    public function execute(array $paths): void
    {
        $keywords = [];

        foreach ($paths as $path) {
            $metadataKeywords = $this->getMetadataKeywords($path);
            if ($metadataKeywords !== null) {
                $keywords[$path] = $metadataKeywords;
            }
        }

        $assets = $this->getAssetsByPaths->execute(array_keys($keywords));

        $assetKeywords = [];

        foreach ($assets as $asset) {
            $assetKeywords[] = $this->assetKeywordsFactory->create([
                'assetId' => $asset->getId(),
                'keywords' => $keywords[$asset->getPath()]
            ]);
        }

        $this->saveAssetKeywords->execute($assetKeywords);
    }

    /**
     * Get keywords from file metadata
     *
     * @param string $path
     * @return KeywordInterface[]|null
     */
    private function getMetadataKeywords(string $path): ?array
    {
        $metadataKeywords = $this->extractMetadata->execute($this->getMediaDirectory()->getAbsolutePath($path))
            ->getKeywords();

        if ($metadataKeywords === null) {
            return null;
        }

        $keywords = [];

        foreach ($metadataKeywords as $keyword) {
            $keywords[] = $this->keywordFactory->create(
                [
                    'keyword' => $keyword
                ]
            );
        }

        return $keywords;
    }

    /**
     * Retrieve media directory instance with read access
     *
     * @return ReadInterface
     */
    private function getMediaDirectory(): ReadInterface
    {
        return $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);
    }
}
