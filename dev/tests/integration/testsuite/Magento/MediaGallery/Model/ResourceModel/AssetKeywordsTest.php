<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaGallery\Model\ResourceModel;

use Behat\Gherkin\Keywords\KeywordsInterface;
use Magento\MediaGalleryApi\Api\Data\KeywordInterfaceFactory;
use Magento\MediaGalleryApi\Api\Data\AssetKeywordsInterfaceFactory;
use Magento\MediaGalleryApi\Api\Data\AssetKeywordsInterface;
use Magento\MediaGalleryApi\Api\GetAssetsByPathsInterface;
use Magento\MediaGalleryApi\Api\GetAssetsKeywordsInterface;
use Magento\MediaGalleryApi\Api\SaveAssetsKeywordsInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Testing assets keywords operation
 */
class AssetKeywordsTest extends TestCase
{
    private const FIXTURE_ASSET_PATH = 'testDirectory/path.jpg';

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
     * @var KeywordInterfaceFactory
     */
    private $keywordFactory;

    /**
     * @var GetAssetsByPathsInterface
     */
    private $getAssetsByPath;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->saveAssetsKeywords = Bootstrap::getObjectManager()->get(SaveAssetsKeywordsInterface::class);
        $this->getAssetsKeywords = Bootstrap::getObjectManager()->get(GetAssetsKeywordsInterface::class);
        $this->assetsKeywordsFactory = Bootstrap::getObjectManager()->get(AssetKeywordsInterfaceFactory::class);
        $this->keywordFactory = Bootstrap::getObjectManager()->get(KeywordInterfaceFactory::class);
        $this->getAssetsByPath = Bootstrap::getObjectManager()->get(GetAssetsByPathsInterface::class);
    }

    /**
     * Testing assets keywords save and get
     *
     * @magentoDataFixture Magento/MediaGallery/_files/media_asset.php
     * @dataProvider keywordsProvider
     * @param array $keywords
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function testSaveAndGetKeywords(array $keywords): void
    {
        $keywords = ['pear', 'plum'];

        $loadedAssets = $this->getAssetsByPath->execute([self::FIXTURE_ASSET_PATH]);
        $this->assertCount(1, $loadedAssets);
        $loadedAsset = current($loadedAssets);

        $assetKeywords = $this->assetsKeywordsFactory->create(
            [
                'assetId' => $loadedAsset->getId(),
                'keywords' => $this->getKeywords($keywords)
            ]
        );

        $this->saveAssetsKeywords->execute([$assetKeywords]);
        $loadedAssetKeywords = $this->getAssetsKeywords->execute([$loadedAsset->getId()]);

        $this->assertCount(1, $loadedAssetKeywords);

        /** @var AssetKeywordsInterface $loadedAssetKeyword */
        $loadedAssetKeyword = current($loadedAssetKeywords);

        $loadedKeywords = $loadedAssetKeyword->getKeywords();

        $this->assertEquals(count($keywords), count($loadedKeywords));

        $loadedKeywordStrings = [];
        foreach ($loadedKeywords as $loadedKeywordObject) {
            $loadedKeywordStrings[] = $loadedKeywordObject->getKeyword();
        }

        sort($loadedKeywordStrings);
        sort($keywords);

        $this->assertEquals($keywords, $loadedKeywordStrings);
    }

    /**
     * Data provider of paths matching existing asset
     *
     * @return array
     */
    public function keywordsProvider(): array
    {
        return [
            [['one-keyword']],
            [['кириллица']],
            [['plum', 'pear']],
            [[]]
        ];
    }

    /**
     * Create keywords
     *
     * @param string[] $keywords
     * @return KeywordsInterface[]
     */
    private function getKeywords(array $keywords): array
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
