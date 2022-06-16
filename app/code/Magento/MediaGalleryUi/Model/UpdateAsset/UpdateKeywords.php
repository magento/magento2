<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGalleryUi\Model\UpdateAsset;

use Magento\MediaGalleryApi\Api\Data\AssetKeywordsInterfaceFactory;
use Magento\MediaGalleryApi\Api\Data\KeywordInterface;
use Magento\MediaGalleryApi\Api\Data\KeywordInterfaceFactory;
use Magento\MediaGalleryApi\Api\SaveAssetsKeywordsInterface;

class UpdateKeywords
{
    /**
     * @var AssetKeywordsInterfaceFactory
     */
    private $assetKeywordsFactory;

    /**
     * @var KeywordInterfaceFactory
     */
    private $keywordFactory;

    /**
     * @var SaveAssetsKeywordsInterface
     */
    private $saveAssetKeywords;

    /**
     * @param AssetKeywordsInterfaceFactory $assetKeywordsFactory
     * @param KeywordInterfaceFactory $keywordFactory
     * @param SaveAssetsKeywordsInterface $saveAssetKeywords
     */
    public function __construct(
        AssetKeywordsInterfaceFactory $assetKeywordsFactory,
        KeywordInterfaceFactory $keywordFactory,
        SaveAssetsKeywordsInterface $saveAssetKeywords
    ) {
        $this->assetKeywordsFactory = $assetKeywordsFactory;
        $this->keywordFactory = $keywordFactory;
        $this->saveAssetKeywords = $saveAssetKeywords;
    }

    /**
     * Save asset keywords
     *
     * @param int $assetId
     * @param string[] $keywords
     */
    public function execute(int $assetId, array $keywords): void
    {
        $this->saveAssetKeywords->execute([
            $this->assetKeywordsFactory->create([
                'assetId' => $assetId,
                'keywords' => $this->createKeywords($keywords)
            ])
        ]);
    }

    /**
     * Create keyword objects from strings
     *
     * @param string[] $keywords
     * @return KeywordInterface[]
     */
    private function createKeywords(array $keywords): array
    {
        $keywordObjects = [];
        foreach ($keywords as $keyword) {
            $keywordObjects[] = $this->keywordFactory->create(
                [
                    'keyword' => $keyword
                ]
            );
        }
        return $keywordObjects;
    }
}
