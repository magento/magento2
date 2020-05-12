<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model;

use Magento\MediaGalleryApi\Api\Data\KeywordInterfaceFactory;
use Magento\MediaGalleryApi\Api\Data\AssetInterfaceFactory;
use Magento\MediaGalleryApi\Api\Data\AssetKeywordsInterfaceFactory;
use Magento\MediaGalleryApi\Api\Data\AssetKeywordsInterface;
use Magento\MediaGalleryApi\Api\GetAssetsByIdsInterface;
use Magento\MediaGalleryApi\Api\GetAssetsByPathsInterface;
use Magento\MediaGalleryApi\Api\GetAssetsKeywordsInterface;
use Magento\MediaGalleryApi\Api\SaveAssetsInterface;
use Magento\MediaGalleryApi\Api\SaveAssetsKeywordsInterface;
use Magento\MediaGalleryApi\Api\DeleteAssetsByPathsInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * End to end test for working with assets and keywords
 */
class AssetEndToEndTest extends TestCase
{
    /**
     * @var GetAssetsKeywordsInterface
     */
    private $saveAssetsKeywords;

    /**
     * @var GetAssetsKeywordsInterface
     */
    private $getAssetsKeywords;

    /**
     * @var AssetKeywordsInterfaceFactory
     */
    private $assetsKeywordsFactory;

    /**
     * @var AssetInterfaceFactory
     */
    private $assetFactory;

    /**
     * @var KeywordInterfaceFactory
     */
    private $keywordFactory;

    /**
     * @var SaveAssetsInterface
     */
    private $saveAssets;

    /**
     * @var GetAssetsByIdsInterface
     */
    private $getAssetsByIds;

    /**
     * @var GetAssetsByPathsInterface
     */
    private $getAssetsByPath;

    /**
     * @var DeleteAssetsByPathsInterface
     */
    private $deleteAssetsByPaths;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->saveAssetsKeywords = Bootstrap::getObjectManager()->get(SaveAssetsKeywordsInterface::class);
        $this->getAssetsKeywords = Bootstrap::getObjectManager()->get(GetAssetsKeywordsInterface::class);
        $this->assetsKeywordsFactory = Bootstrap::getObjectManager()->get(AssetKeywordsInterfaceFactory::class);
        $this->assetFactory = Bootstrap::getObjectManager()->get(AssetInterfaceFactory::class);
        $this->keywordFactory = Bootstrap::getObjectManager()->get(KeywordInterfaceFactory::class);
        $this->saveAssets = Bootstrap::getObjectManager()->get(SaveAssetsInterface::class);
        $this->getAssetsByIds = Bootstrap::getObjectManager()->get(GetAssetsByIdsInterface::class);
        $this->getAssetsByPath = Bootstrap::getObjectManager()->get(GetAssetsByPathsInterface::class);
        $this->deleteAssetsByPaths = Bootstrap::getObjectManager()->get(DeleteAssetsByPathsInterface::class);
    }

    /**
     * Testing assets keywords save and get
     */
    public function testExecute(): void
    {
        $keyword1 = $this->keywordFactory->create(
            [
                'keyword' => 'pear'
            ]
        );

        $keyword2 = $this->keywordFactory->create(
            [
                'keyword' => 'plum'
            ]
        );

        $asset = $this->assetFactory->create(
            [
                'path' => 'fruit.jpg',
                'title' => 'Img',
                'source' => 'Local',
                'contentType' => 'image/jpeg',
                'width' => 420,
                'height' => 240,
                'size' => 12877
            ]
        );
        $this->saveAssets->execute([$asset]);
        $loadedAssets = $this->getAssetsByPath->execute([$asset->getPath()]);
        $loadedAsset = $loadedAssets[0];

        $this->assertCount(1, $loadedAssets);

        $assetKeywords = $this->assetsKeywordsFactory->create(
            [
                'assetId' => $loadedAsset->getId(),
                'keywords' => [
                    $keyword1,
                    $keyword2
                ]
            ]
        );

        $this->saveAssetsKeywords->execute([$assetKeywords]);
        $loadedAssetKeywords = $this->getAssetsKeywords->execute([$loadedAsset->getId()]);

        $this->assertCount(1, $loadedAssetKeywords);

        /** @var AssetKeywordsInterface $loadedAssetKeywords1 */
        $loadedAssetKeywords1 = current($loadedAssetKeywords);

        $loadedKeywords = $loadedAssetKeywords1->getKeywords();

        $this->assertCount(2, $loadedKeywords);

        foreach ($loadedKeywords as $theKeyword) {
            $this->assertTrue(in_array($theKeyword->getKeyword(), ['pear', 'plum']));
        }

        $this->deleteAssetsByPaths->execute(['fruit.jpg']);
    }
}
